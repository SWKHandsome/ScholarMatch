<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarship_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scholarship_id')->constrained()->onDelete('cascade');
            $table->string('required_nationality')->nullable();
            $table->string('required_study_level')->nullable();
            $table->string('required_income_category')->nullable(); // B40, M40, T20
            $table->decimal('max_household_income', 12, 2)->nullable();
            $table->unsignedInteger('min_spm_as')->nullable();
            $table->unsignedInteger('min_spm_credits')->nullable();
            $table->decimal('min_cgpa', 3, 2)->nullable();
            $table->string('required_field_of_study')->nullable();
            $table->string('required_institution_type')->nullable();
            $table->enum('income_rule_type', ['hard', 'soft', 'none'])->default('none');
            $table->enum('study_level_rule_type', ['hard', 'soft', 'none'])->default('none');
            $table->enum('field_rule_type', ['hard', 'soft', 'none'])->default('none');
            $table->enum('institution_rule_type', ['hard', 'soft', 'none'])->default('none');
            $table->json('rule_payload')->nullable();
            $table->timestamps();

            $table->unique('scholarship_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarship_rules');
    }
};