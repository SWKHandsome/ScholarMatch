<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('provider');
            $table->text('description')->nullable();
            $table->enum('award_type', ['Full Scholarship', 'Partial Scholarship', 'Tuition Fee Waiver', 'Living Allowance', 'Loan', 'Convertible Loan']);
            $table->date('deadline');
            $table->string('application_link')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};