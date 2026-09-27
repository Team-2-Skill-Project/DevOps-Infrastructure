<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_skills', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('job_post_id')
                ->constrained('job_posts')
                ->cascadeOnDelete();

            $table
                ->foreignId('skill_id')
                ->constrained('skills')
                ->cascadeOnDelete();

            $table->boolean('is_required')->default(true);
            $table->unsignedTinyInteger('importance')->nullable();

            $table->timestamps();

            $table->unique(
                ['job_post_id', 'skill_id'],
                'job_skills_job_post_skill_unique'
            );

            $table->index('is_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_skills');
    }
};
