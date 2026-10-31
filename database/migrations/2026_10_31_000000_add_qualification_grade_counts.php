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
            $table->enum('education_level', ['SPM', 'IGCSE', 'O-Level', 'STPM', 'STAM', 'A-Level', 'UEC', 'Matriculation', 'Foundation', 'Diploma', 'Undergraduate'])->change();
            $table->string('grading_scale')->nullable();
            $table->json('grade_counts')->nullable();
            $table->string('stam_rank')->nullable();
        });
        Schema::table('scholarship_qualification_routes', function (Blueprint $table) {
            $table->string('grading_scale')->nullable();
            $table->json('grade_requirements')->nullable();
            $table->string('min_stam_rank')->nullable();
            $table->json('uec_average')->nullable();
        });
        // Existing aggregate SPM counts cannot reveal a student's actual grade breakdown.
        // Retain them for reference; students confirm their breakdown on the new form.
    }

    public function down(): void
    {
        $oldLevels = ['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate'];
        if (DB::table('academic_results')->whereNotIn('education_level', $oldLevels)->orWhereNotNull('grade_counts')->orWhereNotNull('stam_rank')->exists()
            || DB::table('scholarship_qualification_routes')->whereNotIn('education_level', $oldLevels)->orWhereNotNull('grade_requirements')->orWhereNotNull('min_stam_rank')->orWhereNotNull('uec_average')->exists()) {
            throw new RuntimeException('Export and preserve new qualification results and rules before rolling back this migration.');
        }
        Schema::table('scholarship_qualification_routes', fn (Blueprint $table) => $table->dropColumn(['grading_scale', 'grade_requirements', 'min_stam_rank', 'uec_average']));
        Schema::table('academic_results', function (Blueprint $table) use ($oldLevels) {
            $table->dropColumn(['grading_scale', 'grade_counts', 'stam_rank']);
            $table->enum('education_level', $oldLevels)->change();
        });
    }
};
