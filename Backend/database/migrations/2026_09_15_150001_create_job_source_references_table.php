<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_source_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_post_id')->constrained()->restrictOnDelete();
            $table->foreignId('raw_job_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('job_source_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('ingestion_run_id');
            $table->string('external_id')->nullable();
            $table->string('source_url', 2048);
            $table->string('detail_url', 2048)->nullable();
            $table->string('match_method', 50);
            $table->json('match_evidence')->nullable();
            $table->timestamps();
            $table->foreign(['ingestion_run_id', 'job_source_id'])->references(['id', 'job_source_id'])->on('ingestion_runs')->restrictOnDelete();
            $table->index(['job_post_id', 'id']);
            $table->index(['job_source_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_source_references');
    }
};
