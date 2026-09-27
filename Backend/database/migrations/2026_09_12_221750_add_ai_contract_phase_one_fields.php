<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_skills', function (Blueprint $table): void {
            $table->decimal('confidence', 3, 2)->nullable();
            $table->json('evidence')->nullable();
        });

        Schema::table('job_skills', function (Blueprint $table): void {
            $table->string('required_level', 50)->nullable();
        });

        Schema::table('job_posts', function (Blueprint $table): void {
            $table->unsignedInteger('min_years_experience')->nullable();
            $table->unsignedInteger('max_years_experience')->nullable();
            $table->json('responsibilities')->nullable();
            $table->string('canonical_role', 150)->nullable();
        });

        Schema::table('experiences', function (Blueprint $table): void {
            $table->json('technologies')->nullable();
        });

        Schema::table('career_preferences', function (Blueprint $table): void {
            $table->json('target_roles')->nullable();
            $table->json('preferred_industries')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('career_preferences', function (Blueprint $table): void {
            $table->dropColumn(['target_roles', 'preferred_industries']);
        });
        Schema::table('experiences', function (Blueprint $table): void {
            $table->dropColumn('technologies');
        });
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->dropColumn(['min_years_experience', 'max_years_experience', 'responsibilities', 'canonical_role']);
        });
        Schema::table('job_skills', function (Blueprint $table): void {
            $table->dropColumn('required_level');
        });
        Schema::table('candidate_skills', function (Blueprint $table): void {
            $table->dropColumn(['confidence', 'evidence']);
        });
    }
};
