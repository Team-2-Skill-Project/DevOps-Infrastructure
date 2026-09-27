<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_jobs', function (Blueprint $table): void {
            $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_post_id')->after('user_id')->constrained('job_posts')->cascadeOnDelete();
            $table->unique(['user_id', 'job_post_id']);
        });
    }

    public function down(): void
    {
        Schema::table('saved_jobs', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'job_post_id']);
            $table->dropForeign(['job_post_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'job_post_id']);
        });
    }
};
