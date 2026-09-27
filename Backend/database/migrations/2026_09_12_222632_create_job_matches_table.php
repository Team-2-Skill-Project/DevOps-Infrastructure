<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_matches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_post_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 5, 2);
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('weak_skills')->nullable();
            $table->json('reasons')->nullable();
            $table->decimal('confidence', 3, 2)->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['candidate_profile_id', 'job_post_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
