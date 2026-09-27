<?php

use App\Enums\ApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_profile_id')->constrained('candidate_profiles')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_posts')->cascadeOnDelete();

            $table->string('status')->default(ApplicationStatus::APPLIED->value);
            $table->text('cover_letter')->nullable();

            $table->timestamps();

            $table->unique(['candidate_profile_id', 'job_id']);

            $table->index(['candidate_profile_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
