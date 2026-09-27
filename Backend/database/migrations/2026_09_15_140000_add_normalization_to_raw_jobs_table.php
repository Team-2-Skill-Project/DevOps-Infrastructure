<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table): void {
            $table->string('normalization_status', 30)->default('pending')->after('extracted_at');
            $table->string('normalization_error_code', 50)->nullable()->after('normalization_status');
            $table->text('normalization_error_message')->nullable()->after('normalization_error_code');
            $table->json('normalized_data')->nullable()->after('normalization_error_message');
            $table->timestamp('normalized_at')->nullable()->after('normalized_data');
            $table->index(['job_source_id', 'normalization_status', 'id']);
        });
    }

    public function down(): void
    {
        Schema::table('raw_jobs', function (Blueprint $table): void {
            $table->dropIndex(['job_source_id', 'normalization_status', 'id']);
            $table->dropColumn([
                'normalization_status',
                'normalization_error_code',
                'normalization_error_message',
                'normalized_data',
                'normalized_at',
            ]);
        });
    }
};
