<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmaps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('target_job_post_id')->nullable()->constrained('job_posts')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('overall_progress', 5, 2)->default(0);
            $table->string('status', 50)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmaps');
    }
};
