<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_results', function (Blueprint $table) {
            $table->string('institution_type')->nullable();
            $table->string('field_of_study')->nullable();
        });
        // Keep legacy values for rollback and students who have not created an academic record.
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('institution_type')->nullable()->change();
            $table->string('field_of_study')->nullable()->change();
        });
        DB::table('student_profiles')->orderBy('id')->chunkById(100, function ($profiles) {
            foreach ($profiles as $profile) {
                DB::table('academic_results')->where('user_id', $profile->user_id)->update([
                    'institution_type' => $profile->institution_type, 'field_of_study' => $profile->field_of_study,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('academic_results')->orderBy('id')->chunkById(100, function ($academics) {
            foreach ($academics as $academic) {
                $values = array_filter([
                    'institution_type' => $academic->institution_type, 'field_of_study' => $academic->field_of_study,
                ], fn ($value) => $value !== null);
                if ($values) {
                    DB::table('student_profiles')->where('user_id', $academic->user_id)->update($values);
                }
            }
        });
        // Blank legacy fields remain visibly unanswered when restoring the old required form.
        foreach (['institution_type', 'field_of_study'] as $field) {
            DB::table('student_profiles')->whereNull($field)->update([$field => '']);
        }
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('institution_type')->nullable(false)->change();
            $table->string('field_of_study')->nullable(false)->change();
        });
        Schema::table('academic_results', fn (Blueprint $table) => $table->dropColumn(['institution_type', 'field_of_study']));
    }
};
