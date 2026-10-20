<?php

use App\Models\AcademicResult;
use App\Models\RecommendationLog;
use App\Models\Scholarship;
use App\Models\ScholarshipQualificationRoute;
use App\Models\ScholarshipRule;
use App\Models\User;
use App\Services\ScholarshipRecommendationService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ScholarshipCatalog;
use Database\Seeders\ScholarshipCatalogSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

test('catalogue contains seventy distinct fictional demo awards and supported rule values', function () {
    $entries = collect(ScholarshipCatalog::entries());
    expect($entries)->toHaveCount(70)
        ->and($entries->pluck('catalog_key')->unique())->toHaveCount(70)
        ->and($entries->pluck('name')->unique())->toHaveCount(70);

    foreach ($entries as $entry) {
        $validator = Validator::make($entry, [
            'catalog_key' => 'required|regex:/^[a-z0-9-]+$/',
            'name' => 'required|string|max:255', 'provider' => 'required|string|max:255',
            'description' => 'required|string', 'source_url' => 'prohibited',
            'source_checked_at' => 'prohibited',
            'application_link' => 'required|url:https|max:255',
            'application_notes' => 'required|string|max:2000',
            'special_requirements' => 'present|nullable|string|max:5000',
            'deadline' => 'nullable|date_format:Y-m-d|after:2026-10-20', 'is_active' => 'required|boolean|accepted',
            'study_country' => 'required|in:Malaysia',
            'deadline_type' => 'required|in:fixed,admission,relative,rolling,intake,automatic',
            'award_type' => 'required|in:Full Scholarship,Partial Scholarship,Tuition Fee Waiver,Living Allowance,Loan,Convertible Loan',
            'rules.required_nationality' => 'nullable|in:Malaysian,Non-Malaysian',
            'rules.required_study_level' => 'nullable|in:'.implode(',', AcademicResult::EDUCATION_LEVELS),
            'rules.required_institution_type' => 'nullable|in:'.implode(',', config('scholarship.institution_types')),
            'rules.required_fields_of_study.*' => 'in:'.implode(',', config('scholarship.fields_of_study')),
            'rules.qualification_routes' => 'present|array',
            'rules.qualification_routes.*.education_level' => 'required|in:'.implode(',', AcademicResult::EDUCATION_LEVELS),
            'rules.qualification_routes.*.study_status' => 'required|in:any,studying,completed',
            'rules.qualification_routes.*.min_cgpa' => 'nullable|numeric|between:0,4',
        ]);
        expect($validator->errors()->all())->toBe([], $entry['catalog_key']);
        expect($entry['name'])->toStartWith('Demo ');
        expect($entry['description'])->toStartWith('Fictional demonstration award');
        expect(parse_url($entry['application_link'], PHP_URL_HOST))->toBe('scholarmatch.example');
    }
    ScholarshipCatalog::validateReplacement($entries->all(), '2026-10-20');
});

test('replacement removes the old catalogue and related history but preserves student data', function () {
    $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
    seedIncomeCategories();
    $student = makeStudent();
    $old = makeScholarship(['name' => 'Old demonstration record']);
    $student->savedScholarships()->create(['scholarship_id' => $old->id]);
    RecommendationLog::create(['user_id' => $student->id, 'scholarship_id' => $old->id, 'status' => 'Eligible']);
    $profile = $student->studentProfile->getAttributes();
    $academic = $student->academicResult->getAttributes();

    ScholarshipCatalog::replace();

    expect(Scholarship::count())->toBe(70)
        ->and(ScholarshipRule::count())->toBe(70)
        ->and(Scholarship::find($old->id))->toBeNull()
        ->and($student->savedScholarships()->count())->toBe(0)
        ->and(RecommendationLog::count())->toBe(0)
        ->and($student->fresh()->studentProfile->getAttributes())->toBe($profile)
        ->and($student->fresh()->academicResult->getAttributes())->toBe($academic);
    $recommendations = app(ScholarshipRecommendationService::class)->getRecommendations($student)['recommendations'];
    expect($recommendations)->toHaveCount(70);
    expect(collect($recommendations)->where('status', 'Eligible')->count()
        + collect($recommendations)->where('status', 'Not Eligible')->count())->toBe(70);
});

test('expired replacement data is rejected before any scholarships are removed', function () {
    $this->travelTo(now()->setDate(2100, 1, 1)->startOfDay());
    $old = makeScholarship();
    expect(fn () => ScholarshipCatalog::replace())->toThrow(InvalidArgumentException::class)
        ->and($old->fresh())->not->toBeNull()
        ->and(Scholarship::count())->toBe(1);
});

test('rolling and automatic awards show their actual deadline basis', function () {
    seedIncomeCategories();
    $student = makeStudent();
    $rolling = makeScholarship(['name' => 'Verified rolling award', 'deadline' => null, 'deadline_type' => 'rolling', 'application_notes' => 'The provider accepts applications with no closing date.']);
    $automatic = makeScholarship(['name' => 'Verified automatic award', 'deadline' => null, 'deadline_type' => 'automatic']);
    expect($rolling->deadlineLabel())->toBe('Rolling applications')
        ->and($automatic->deadlineLabel())->toBe('Automatic award');
    $this->actingAs($student)->get(route('student.recommendations'))->assertOk()
        ->assertSee('Rolling applications')->assertSee('Automatic award');
    $this->get(route('student.recommendations.show', $rolling))->assertOk()->assertSee('Rolling applications');
    $student->savedScholarships()->create(['scholarship_id' => $automatic->id]);
    $this->get(route('student.saved-scholarships.index'))->assertOk()->assertSee('Automatic award');
});

test('catalogue reseeding restores the snapshot without duplicate scholarships or lost bookmarks', function () {
    seedIncomeCategories();
    $user = makeStudent();
    $custom = makeScholarship(['name' => 'Administrator scholarship']);
    $this->seed(ScholarshipCatalogSeeder::class);
    $catalog = Scholarship::whereNotNull('catalog_key')->orderBy('catalog_key')->get();
    $ids = $catalog->pluck('id', 'catalog_key')->all();
    $routeCount = ScholarshipQualificationRoute::count();
    $scholarship = $catalog->first();
    $user->savedScholarships()->create(['scholarship_id' => $scholarship->id]);
    RecommendationLog::create(['user_id' => $user->id, 'scholarship_id' => $scholarship->id, 'status' => 'Eligible']);
    $scholarship->update(['name' => 'Edited catalogue title']);
    $scholarship->rule->update(['required_income_category' => 'T20', 'max_household_income' => 123, 'min_cgpa' => 4]);
    Cache::forever('recommendation_catalog_version', 'before-reseed');

    $this->seed(ScholarshipCatalogSeeder::class);

    expect(Scholarship::count())->toBe(71)
        ->and(ScholarshipRule::count())->toBe(71)
        ->and(ScholarshipQualificationRoute::count())->toBe($routeCount)
        ->and(Scholarship::whereNotNull('catalog_key')->orderBy('catalog_key')->pluck('id', 'catalog_key')->all())->toBe($ids)
        ->and($user->savedScholarships()->where('scholarship_id', $scholarship->id)->exists())->toBeTrue()
        ->and(RecommendationLog::where('scholarship_id', $scholarship->id)->count())->toBe(1)
        ->and($custom->fresh()->name)->toBe('Administrator scholarship')
        ->and($scholarship->fresh()->name)->not->toBe('Edited catalogue title')
        ->and($scholarship->fresh()->rule->required_income_category)->toBeNull()
        ->and($scholarship->fresh()->rule->max_household_income)->not->toBe('123.00')
        ->and($scholarship->fresh()->rule->min_cgpa)->toBeNull()
        ->and(Cache::get('recommendation_catalog_version'))->not->toBe('before-reseed');
});

test('full initial seeding can be repeated on a fresh device', function () {
    $this->seed(DatabaseSeeder::class);
    $first = Scholarship::orderBy('catalog_key')->get()->map->only([
        'id', 'catalog_key', 'name', 'provider', 'description', 'award_type', 'deadline',
        'application_link', 'source_url', 'source_checked_at', 'application_notes', 'special_requirements', 'is_active',
    ])->all();
    $this->seed(DatabaseSeeder::class);
    expect(Scholarship::count())->toBe(70)->and(ScholarshipRule::count())->toBe(70)->and(User::count())->toBe(3);
    expect(Scholarship::orderBy('catalog_key')->get()->map->only(array_keys($first[0]))->all())->toEqual($first);
});

test('only unmodified legacy demo records are retired and history is preserved', function () {
    $user = User::factory()->create();
    $original = [
        'name' => 'JPA B40 Undergraduate Scholarship', 'provider' => 'Jabatan Perkhidmatan Awam (JPA)',
        'description' => 'Full scholarship for B40 students pursuing undergraduate studies in local public universities.',
        'award_type' => 'Full Scholarship', 'deadline' => '2026-12-31',
        'application_link' => 'https://www.jpa.gov.my', 'is_active' => true,
    ];
    $demo = Scholarship::create($original);
    $edited = Scholarship::create(array_merge($original, ['description' => 'Administrator edited this record.']));
    $user->savedScholarships()->create(['scholarship_id' => $demo->id]);
    $this->seed(ScholarshipCatalogSeeder::class);
    expect($demo->fresh()->is_active)->toBeFalse()->and($edited->fresh()->is_active)->toBeTrue()
        ->and($user->savedScholarships()->where('scholarship_id', $demo->id)->exists())->toBeTrue()
        ->and(Scholarship::whereNotNull('catalog_key')->count())->toBe(70);
});

test('unknown dates require provider verification and sort after confirmed deadlines', function () {
    $this->travelTo(now()->setDate(2026, 10, 20)->startOfDay());
    seedIncomeCategories();
    $user = makeStudent();
    $unknown = makeScholarship(['name' => 'Intake-dependent award', 'deadline' => null]);
    $dated = makeScholarship(['name' => 'Dated award', 'deadline' => '2026-11-08']);
    makeScholarship(['name' => 'Closed award', 'deadline' => '2026-10-19']);
    makeScholarship(['name' => 'Inactive award', 'deadline' => null, 'is_active' => false]);
    $result = app(ScholarshipRecommendationService::class)->getRecommendations($user)['recommendations'];
    expect(array_column($result, 'scholarship_id'))->toBe([$dated->id, $unknown->id])
        ->and($result[1]['scholarship_data']['deadline'])->toBeNull()
        ->and($result[1]['requires_verification'])->toBeTrue();
    $deadline = collect($result[1]['requirement_results'])->firstWhere('key', 'deadline');
    expect($deadline['outcome'])->toBe('not_applicable')
        ->and($result[1]['special_requirements'])->toContain('Application dates must be confirmed');
    $this->actingAs($user)->get(route('student.recommendations'))->assertOk()->assertSee('Check provider')->assertSee('Check Provider');
});

test('source links and unknown deadlines render across detail saved and admin pages', function () {
    seedIncomeCategories();
    $user = makeStudent();
    $scholarship = makeScholarship([
        'deadline' => null, 'source_url' => 'https://www.bnm.gov.my/careers/scholarships',
        'source_checked_at' => '2026-10-20', 'application_notes' => 'Confirm the intake date.',
    ]);
    $this->actingAs($user)->get(route('student.recommendations.show', $scholarship))->assertOk()
        ->assertSee('Check provider')->assertSee('Official scholarship source')->assertSee('20 Oct 2026')
        ->assertSee('Confirm the intake date.');
    $user->savedScholarships()->create(['scholarship_id' => $scholarship->id]);
    $this->get(route('student.saved-scholarships.index'))->assertOk()->assertSee('Check provider');
    $this->actingAs(makeAdmin())->get(route('admin.scholarships.index'))->assertOk()->assertSee('Check provider');
    $this->get(route('admin.scholarships.edit', $scholarship))->assertOk()->assertSee('Official scholarship source');
    $this->put(route('admin.scholarships.update', $scholarship), array_merge(
        $scholarship->only(['name', 'provider', 'award_type', 'application_link']),
        ['deadline' => '', 'application_notes' => 'Intake is unconfirmed.', 'is_active' => true]
    ))->assertSessionHasNoErrors();
    expect($scholarship->fresh()->deadline)->toBeNull()->and($scholarship->fresh()->application_notes)->toBe('Intake is unconfirmed.');
});
