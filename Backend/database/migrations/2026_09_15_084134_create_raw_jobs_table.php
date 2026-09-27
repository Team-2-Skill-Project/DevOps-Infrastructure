<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingestion_runs', function (Blueprint $table): void {
            $table->unique(['id', 'job_source_id'], 'ingestion_runs_id_source_unique');
        });
        Schema::create('raw_jobs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_source_id')->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('ingestion_run_id');
            $table->char('identity_key', 64);
            $table->string('external_id')->nullable();
            $table->string('source_url', 2048);
            $table->string('detail_url', 2048)->nullable();
            $table->string('discovered_title')->nullable();
            $table->string('discovered_company_name')->nullable();
            $table->json('discovery_metadata')->nullable();
            $table->json('raw_payload')->nullable();
            $table->longText('raw_text')->nullable();
            $table->string('extraction_status', 30)->default('pending');
            $table->string('extraction_error_code', 50)->nullable();
            $table->text('extraction_error_message')->nullable();
            $table->json('extracted_data')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamp('discovered_at');
            $table->timestamps();
            $table->index(['ingestion_run_id', 'job_source_id'], 'raw_jobs_run_source_index');
            $table->foreign(['ingestion_run_id', 'job_source_id'])->references(['id', 'job_source_id'])->on('ingestion_runs')->restrictOnDelete();
            $table->unique(['ingestion_run_id', 'identity_key']);
            $table->index(['job_source_id', 'extraction_status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_jobs');
        Schema::table('ingestion_runs', function (Blueprint $table): void {
            $table->dropUnique('ingestion_runs_id_source_unique');
        });
    }
};
