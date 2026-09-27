<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table): void {
            $table->string('deduplication_status', 30)->default('pending')->after('normalized_at');
            $table->char('fingerprint', 64)->nullable()->after('deduplication_status');
            $table->unsignedSmallInteger('fingerprint_version')->nullable()->after('fingerprint');
            $table->foreignId('canonical_job_post_id')->nullable()->after('fingerprint_version')->constrained('job_posts')->nullOnDelete();
            $table->string('deduplication_method', 50)->nullable()->after('canonical_job_post_id');
            $table->json('deduplication_evidence')->nullable()->after('deduplication_method');
            $table->timestamp('deduplicated_at')->nullable()->after('deduplication_evidence');
            $table->index(['job_source_id', 'deduplication_status', 'id']);
            $table->index(['fingerprint', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('canonical_job_post_id');
            $table->dropIndex(['job_source_id', 'deduplication_status', 'id']);
            $table->dropIndex(['fingerprint', 'id']);
            $table->dropColumn([
                'deduplication_status',
                'fingerprint',
                'fingerprint_version',
                'deduplication_method',
                'deduplication_evidence',
                'deduplicated_at',
            ]);
        });
    }
};
