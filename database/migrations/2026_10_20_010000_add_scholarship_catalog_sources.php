<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarships', function (Blueprint $table) {
            $table->date('deadline')->nullable()->change();
            $table->string('catalog_key')->nullable()->unique();
            $table->text('source_url')->nullable();
            $table->date('source_checked_at')->nullable();
            $table->text('application_notes')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('scholarships')->whereNull('deadline')->exists()) {
            throw new RuntimeException('Cannot restore required deadlines while unknown dates exist. Supply verified deadlines before rolling back.');
        }

        Schema::table('scholarships', function (Blueprint $table) {
            $table->dropUnique(['catalog_key']);
            $table->dropColumn(['catalog_key', 'source_url', 'source_checked_at', 'application_notes']);
            $table->date('deadline')->nullable(false)->change();
        });
    }
};
