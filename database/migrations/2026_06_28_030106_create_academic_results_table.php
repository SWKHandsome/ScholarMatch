<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('education_level', ['SPM', 'STPM', 'Foundation', 'Matriculation', 'Diploma', 'Undergraduate']);
            $table->unsignedInteger('spm_as')->nullable();
            $table->unsignedInteger('spm_credits')->nullable();
            $table->decimal('cgpa', 3, 2)->nullable();
            $table->enum('result_status', ['official', 'pending'])->default('official');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_results');
    }
};