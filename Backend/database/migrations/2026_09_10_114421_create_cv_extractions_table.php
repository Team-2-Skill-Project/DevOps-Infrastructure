<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_extractions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cv_document_id')
                ->constrained('cv_documents')
                ->cascadeOnDelete();

            $table->unsignedInteger('attempt_number')->default(1);

            $table->string('status')->default('pending');
            // pending | processing | completed | failed

            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('parser_version')->nullable();

            $table->longText('raw_text')->nullable();

            $table->json('extracted_data')->nullable();

            $table->decimal('confidence_score', 5, 4)->nullable();

            $table->text('error_message')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(
                ['cv_document_id', 'attempt_number'],
                'cv_extractions_document_attempt_unique'
            );

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_extractions');
    }
};
