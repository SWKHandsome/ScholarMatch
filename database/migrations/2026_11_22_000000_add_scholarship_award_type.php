<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_AWARD_TYPES = [
        'Full Scholarship',
        'Partial Scholarship',
        'Tuition Fee Waiver',
        'Living Allowance',
        'Loan',
        'Convertible Loan',
    ];

    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->enum('award_type', [...self::LEGACY_AWARD_TYPES, 'Scholarship'])->change();
        });
    }

    public function down(): void
    {
        if (DB::table('scholarships')->whereNotIn('award_type', self::LEGACY_AWARD_TYPES)->exists()) {
            throw new RuntimeException('Cannot restore the previous award types while scholarships use an award type absent from that schema.');
        }

        Schema::table('scholarships', function (Blueprint $table) {
            $table->enum('award_type', self::LEGACY_AWARD_TYPES)->change();
        });
    }
};
