<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nationality');
            $table->string('state');
            $table->decimal('household_income', 12, 2);
            $table->unsignedInteger('number_of_dependents')->default(0);
            $table->string('income_category')->nullable(); // B40, M40, T20
            $table->string('institution_type');
            $table->string('field_of_study');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};