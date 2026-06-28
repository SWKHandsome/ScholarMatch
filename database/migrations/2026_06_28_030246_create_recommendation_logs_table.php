<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('scholarship_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('score')->default(0);
            $table->enum('status', ['Eligible', 'Partially Eligible', 'Not Suitable']);
            $table->json('failed_hard_rules')->nullable();
            $table->json('explanation')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_logs');
    }
};