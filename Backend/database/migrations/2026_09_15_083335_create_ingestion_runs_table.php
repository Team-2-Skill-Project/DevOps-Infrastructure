<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingestion_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_source_id')->constrained()->restrictOnDelete();
            $table->string('trigger_type', 30);
            $table->string('status', 30)->default('running');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            foreach (['discovered_count', 'fetched_count', 'created_count', 'updated_count', 'skipped_count', 'failed_count'] as $counter) {
                $table->unsignedInteger($counter)->default(0);
            }
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->json('error_context')->nullable();
            $table->timestamps();
            $table->index(['job_source_id', 'started_at']);
            $table->index(['job_source_id', 'finished_at', 'id']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingestion_runs');
    }
};
