<?php

use App\Models\StudyField;
use App\Models\User;
use App\Services\DatabaseSnapshot;
use App\Services\SqliteDatabaseTransfer;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('Run with the dedicated MySQL test database; see docs/MYSQL_SETUP.md.');
    }
    $this->sourcePath = tempnam(sys_get_temp_dir(), 'scholarmatch-transfer-');
    config(['database.connections.transfer_source' => [
        'driver' => 'sqlite', 'database' => $this->sourcePath, 'prefix' => '', 'foreign_key_constraints' => true,
    ]]);
    Artisan::call('migrate', ['--database' => 'transfer_source', '--force' => true]);
    seedIncomeCategories();
    $user = makeStudent([], ['cgpa' => 3.25]);
    $award = makeScholarship(['catalog_key' => null]);
    $user->savedScholarships()->create(['scholarship_id' => $award->id]);
    $this->originalHash = $user->password;
    $this->userId = $user->id;
    DB::connection('transfer_source')->table('study_fields')->delete();
    foreach (SqliteDatabaseTransfer::TABLES as $table) {
        foreach (DB::table($table)->get() as $row) {
            DB::connection('transfer_source')->table($table)->insert((array) $row);
        }
    }
    foreach (array_reverse(SqliteDatabaseTransfer::TABLES) as $table) {
        DB::table($table)->delete();
    }
});

afterEach(function () {
    if (isset($this->sourcePath)) {
        DB::disconnect('transfer_source');
        unlink($this->sourcePath);
    }
});

test('SQLite transfer preserves IDs password hashes dates decimals JSON and relationships', function () {
    $counts = app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection());
    expect($counts['users'])->toBe(1)
        ->and(User::findOrFail($this->userId)->password)->toBe($this->originalHash)
        ->and(DB::table('academic_results')->value('cgpa'))->toBe('3.25')
        ->and(DB::table('saved_scholarships')->count())->toBe(1);
    expect(fn () => app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection()))
        ->toThrow(RuntimeException::class, 'not empty');
});

test('SQLite transfer dry run does not import any rows', function () {
    $counts = app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection(), true);
    expect($counts['users'])->toBe(1)->and(User::count())->toBe(0);
});

test('SQLite transfer replaces unused migration defaults and preserves custom field settings', function () {
    DB::table('study_fields')->insert(StudyField::defaultRows());
    DB::connection('transfer_source')->table('study_fields')->where('name', 'Business')->update(['name' => 'Business Studies', 'is_active' => false]);
    DB::connection('transfer_source')->table('study_fields')->insert(['id' => 99, 'name' => 'Artificial Intelligence', 'seed_name' => null, 'is_active' => true]);
    $counts = app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection());
    expect($counts['study_fields'])->toBe(22)->and(StudyField::findOrFail(99)->name)->toBe('Artificial Intelligence')
        ->and(StudyField::where('seed_name', 'Business')->firstOrFail()->name)->toBe('Business Studies')
        ->and(StudyField::where('seed_name', 'Business')->firstOrFail()->is_active)->toBeFalse();
});

test('SQLite transfer refuses to replace edited field settings on a destination', function () {
    StudyField::create(['name' => 'Destination custom field', 'is_active' => true]);
    expect(fn () => app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection()))
        ->toThrow(RuntimeException::class, 'not empty');
    expect(StudyField::count())->toBe(1)->and(User::count())->toBe(0);
});

test('SQLite transfer rolls back all rows when a later record is invalid for MySQL', function () {
    DB::connection('transfer_source')->table('student_profiles')->update(['state' => str_repeat('X', 300)]);
    expect(fn () => app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection()))->toThrow(QueryException::class);
    expect(User::count())->toBe(0)->and(DB::table('scholarships')->count())->toBe(0);
});

test('SQLite transfer refuses a mismatched migration history before inserting rows', function () {
    DB::connection('transfer_source')->table('migrations')->where('id', '>', 1)->delete();
    expect(fn () => app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection()))
        ->toThrow(RuntimeException::class, 'same completed migrations');
    expect(User::count())->toBe(0);
});

test('MySQL backup contains parseable verified data and does not mutate the database', function () {
    app(SqliteDatabaseTransfer::class)->transfer($this->sourcePath, DB::connection());
    $path = app(DatabaseSnapshot::class)->create(DB::connection());
    try {
        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        expect($data['format'])->toBe('scholarmatch-data-backup-v1')
            ->and($data['tables']['users'][0]['password'])->toBe($this->originalHash)
            ->and(count($data['tables']['saved_scholarships']))->toBe(1)
            ->and(User::count())->toBe(1);
    } finally {
        unlink($path);
    }
});
