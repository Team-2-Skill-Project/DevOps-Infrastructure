<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_milestones', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('roadmap_phase_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('milestone_order');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['roadmap_phase_id', 'milestone_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_milestones');
    }
};
