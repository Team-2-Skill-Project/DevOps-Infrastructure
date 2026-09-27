<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('base_url', 2048)->nullable();
            $table->string('source_type', 30);
            $table->string('collection_method', 30);
            $table->boolean('is_active')->default(true);
            $table->boolean('schedule_enabled')->default(false);
            $table->string('schedule_expression')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['is_active', 'schedule_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_sources');
    }
};
