<?php

use App\Models\StudyField;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('study_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('seed_name')->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        $now = now();
        DB::table('study_fields')->insert(array_map(fn ($row) => $row + ['created_at' => $now, 'updated_at' => $now], StudyField::defaultRows()));

        // Preserve any previously used custom labels without changing existing records.
        $used = DB::table('academic_results')->pluck('field_of_study')
            ->merge(DB::table('student_profiles')->pluck('field_of_study'))
            ->merge(DB::table('scholarship_rules')->pluck('required_field_of_study'));
        foreach (DB::table('scholarship_rules')->whereNotNull('required_fields_of_study')->pluck('required_fields_of_study') as $json) {
            $used = $used->merge(json_decode($json, true, 512, JSON_THROW_ON_ERROR) ?? []);
        }
        foreach ($used->filter(fn ($name) => is_string($name) && trim($name) !== '')->unique() as $name) {
            DB::table('study_fields')->insertOrIgnore(['name' => $name, 'seed_name' => null, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
        }
    }

    public function down(): void
    {
        if (DB::table('study_fields')->count() !== count(StudyField::defaultRows())
            || DB::table('study_fields')->whereNull('seed_name')->orWhereColumn('name', '!=', 'seed_name')->orWhere('is_active', false)->exists()) {
            throw new RuntimeException('Preserve custom, renamed or inactive study fields before rolling back this migration.');
        }
        Schema::dropIfExists('study_fields');
    }
};
