<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_posts', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table
                ->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description');

            $table->string('employment_type')->nullable();
            $table->string('work_mode')->nullable();
            $table->string('experience_level')->nullable();

            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();

            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->nullable();

            $table->string('application_url')->nullable();

            $table->string('source')->default('manual');
            $table->string('external_id')->nullable();
            $table->string('external_url')->nullable();

            $table->string('status')->default('draft');

            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['country', 'city']);
            $table->index(['employment_type', 'work_mode']);
            $table->index('source');

            $table->unique(
                ['source', 'external_id'],
                'job_posts_source_external_id_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_posts');
    }
};
