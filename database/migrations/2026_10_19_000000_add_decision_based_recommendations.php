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
            $table->string('study_status')->nullable();
            $table->unsignedTinyInteger('year_of_study')->nullable();
            $table->string('funding_study_level')->nullable();
        });
        Schema::table('scholarships', fn (Blueprint $table) => $table->text('special_requirements')->nullable());
        Schema::table('scholarship_rules', function (Blueprint $table) {
            $table->json('accepted_education_levels')->nullable();
            $table->string('required_study_status')->nullable();
            $table->unsignedTinyInteger('min_year_of_study')->nullable();
            $table->unsignedTinyInteger('max_year_of_study')->nullable();
        });
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->string('status')->change();
            $table->string('evaluation_version')->nullable();
            $table->json('requirement_results')->nullable();
            $table->json('failed_requirements')->nullable();
            $table->boolean('requires_verification')->default(false);
            $table->text('special_requirements')->nullable();
            $table->json('legacy_evaluation')->nullable();
        });
        // Preserve old evaluations; old score labels are not new eligibility decisions.
        DB::table('recommendation_logs')->orderBy('id')->chunkById(100, function ($logs) {
            foreach ($logs as $log) {
                DB::table('recommendation_logs')->where('id', $log->id)->update([
                    'evaluation_version' => 'legacy-score-v4',
                    'legacy_evaluation' => json_encode([
                        'score' => $log->score, 'status' => $log->status,
                        'failed_hard_rules' => json_decode($log->failed_hard_rules ?? '[]', true),
                        'score_breakdown' => json_decode($log->score_breakdown ?? '[]', true),
                        'explanation' => json_decode($log->explanation ?? '[]', true),
                        'evaluated_at' => $log->updated_at,
                    ], JSON_THROW_ON_ERROR),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('recommendation_logs')->whereNotNull('legacy_evaluation')->orderBy('id')->chunkById(100, function ($logs) {
            foreach ($logs as $log) {
                $legacy = json_decode($log->legacy_evaluation, true, 512, JSON_THROW_ON_ERROR);
                DB::table('recommendation_logs')->where('id', $log->id)->update([
                    'score' => $legacy['score'], 'status' => $legacy['status'],
                    'failed_hard_rules' => json_encode($legacy['failed_hard_rules'], JSON_THROW_ON_ERROR),
                    'score_breakdown' => json_encode($legacy['score_breakdown'], JSON_THROW_ON_ERROR),
                    'explanation' => json_encode($legacy['explanation'], JSON_THROW_ON_ERROR),
                ]);
            }
        });
        DB::table('recommendation_logs')->where('status', 'Not Eligible')->update(['status' => 'Not Suitable']);
        Schema::table('recommendation_logs', function (Blueprint $table) {
            $table->dropColumn(['evaluation_version', 'requirement_results', 'failed_requirements',
                'requires_verification', 'special_requirements', 'legacy_evaluation']);
            $table->enum('status', ['Eligible', 'Partially Eligible', 'Not Suitable'])->change();
        });
        Schema::table('scholarship_rules', fn (Blueprint $table) => $table->dropColumn([
            'accepted_education_levels', 'required_study_status', 'min_year_of_study', 'max_year_of_study',
        ]));
        Schema::table('scholarships', fn (Blueprint $table) => $table->dropColumn('special_requirements'));
        Schema::table('academic_results', fn (Blueprint $table) => $table->dropColumn([
            'study_status', 'year_of_study', 'funding_study_level',
        ]));
    }
};
