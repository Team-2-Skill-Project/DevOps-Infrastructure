<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_phases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('phase_order');
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('rationale')->nullable();
            $table->decimal('progress', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['roadmap_id', 'phase_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_phases');
    }
};
