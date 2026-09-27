<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_profile_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('target_role')->nullable();
            $table->string('job_type')->nullable();
            $table->string('work_mode')->nullable();
            $table->string('preferred_country')->nullable();
            $table->string('preferred_city')->nullable();
            $table->string('experience_level')->nullable();
            $table->text('career_goal')->nullable();
            $table->boolean('open_to_relocation')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_preferences');
    }
};
