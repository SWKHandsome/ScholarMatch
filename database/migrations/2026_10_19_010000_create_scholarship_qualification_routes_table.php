<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_qualification_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_rule_id')->constrained()->cascadeOnDelete();
            $table->string('education_level');
            $table->string('study_status');
            $table->unsignedTinyInteger('min_spm_as')->nullable();
            $table->unsignedTinyInteger('min_spm_credits')->nullable();
            $table->decimal('min_cgpa', 3, 2)->nullable();
            $table->unsignedTinyInteger('min_year_of_study')->nullable();
            $table->unsignedTinyInteger('max_year_of_study')->nullable();
            $table->timestamps();
        });

        $allLevels = ['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'];
        DB::table('scholarship_rules')->orderBy('id')->chunkById(100, function ($rules) use ($allLevels) {
            foreach ($rules as $rule) {
                $levels = json_decode($rule->accepted_education_levels ?? '[]', true) ?: [];
                if (! $levels) {
                    if ($rule->min_spm_as !== null || $rule->min_spm_credits !== null) {
                        $levels[] = 'SPM';
                    }
                    if ($rule->min_cgpa !== null) {
                        $levels = array_merge($levels, array_values(array_diff($allLevels, ['SPM'])));
                    }
                }
                if (! $levels && ($rule->required_study_status || $rule->min_year_of_study !== null || $rule->max_year_of_study !== null)) {
                    $levels = $allLevels;
                }
                foreach ($levels as $level) {
                    DB::table('scholarship_qualification_routes')->insert([
                        'scholarship_rule_id' => $rule->id, 'education_level' => $level,
                        'study_status' => $rule->required_study_status ?: ($level === 'SPM' ? 'completed' : 'any'),
                        'min_spm_as' => $level === 'SPM' ? $rule->min_spm_as : null,
                        'min_spm_credits' => $level === 'SPM' ? $rule->min_spm_credits : null,
                        'min_cgpa' => $level !== 'SPM' ? $rule->min_cgpa : null,
                        'min_year_of_study' => $rule->min_year_of_study,
                        'max_year_of_study' => $rule->max_year_of_study,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_qualification_routes');
    }
};
