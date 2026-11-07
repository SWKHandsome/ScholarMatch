<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_rules', function (Blueprint $table) {
            $table->json('required_fields_of_study')->nullable()->after('required_field_of_study');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_rules', function (Blueprint $table) {
            $table->dropColumn('required_fields_of_study');
        });
    }
};
