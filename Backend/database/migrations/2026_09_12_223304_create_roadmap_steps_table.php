<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('target_skill_id')->nullable()->constrained('skills')->nullOnDelete();
            $table->string('status', 50)->default('pending');
            $table->json('resources')->nullable();
            $table->timestamps();

            $table->unique(['roadmap_id', 'step_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_steps');
    }
};
