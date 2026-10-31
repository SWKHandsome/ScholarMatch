<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_qualification_routes', function (Blueprint $table) {
            $table->json('stpm_average')->nullable();
            $table->json('uec_total')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('scholarship_qualification_routes')->whereNotNull('stpm_average')->orWhereNotNull('uec_total')->exists()) {
            throw new RuntimeException('Preserve STPM average and UEC total-point rules before rolling back this migration.');
        }
        Schema::table('scholarship_qualification_routes', fn (Blueprint $table) => $table->dropColumn(['stpm_average', 'uec_total']));
    }
};
