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
            $table->string('stpm_pa_grade')->nullable();
            $table->boolean('stpm_cgpa_confirmed')->default(false);
        });
    }

    public function down(): void
    {
        if (DB::table('academic_results')->whereNotNull('stpm_pa_grade')->orWhere('stpm_cgpa_confirmed', true)->exists()) {
            throw new RuntimeException('Preserve Pengajian Am grades and STPM CGPA confirmations before rolling back this migration.');
        }
        Schema::table('academic_results', fn (Blueprint $table) => $table->dropColumn(['stpm_pa_grade', 'stpm_cgpa_confirmed']));
    }
};
