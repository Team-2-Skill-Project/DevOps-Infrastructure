<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cv_documents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('candidate_profile_id')
                ->constrained('candidate_profiles')
                ->cascadeOnDelete();

            $table->string('original_filename');
            $table->string('storage_disk')->default('local');
            $table->string('storage_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->string('file_hash', 64)->nullable();

            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_current')->default(true);

            $table->string('status')->default('uploaded');
            // uploaded | processing | processed | failed

            $table->timestamp('processed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();

            $table->unique(
                ['candidate_profile_id', 'version'],
                'cv_documents_profile_version_unique'
            );

            $table->index(['candidate_profile_id', 'is_current']);
            $table->index('status');
            $table->index('file_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cv_documents');
    }
};
