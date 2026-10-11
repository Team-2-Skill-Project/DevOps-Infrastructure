<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmaps', function (Blueprint $table): void {
            $table->foreignId('previous_roadmap_id')->nullable()->after('id')->constrained('roadmaps')->nullOnDelete();
            $table->string('target_role', 150)->nullable()->after('target_job_post_id');
            $table->text('rationale')->nullable()->after('description');
            $table->string('generation_version', 100)->nullable()->after('status');
            $table->timestamp('generated_at')->nullable()->after('generation_version');
            $table->timestamp('refreshed_at')->nullable()->after('generated_at');
            $table->decimal('progress_at_generation', 5, 2)->default(0)->after('refreshed_at');
            $table->json('generation_input_snapshot')->nullable()->after('progress_at_generation');

            $table->index(['candidate_profile_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('roadmaps', function (Blueprint $table): void {
            $table->dropIndex(['candidate_profile_id', 'status', 'created_at']);
            $table->dropConstrainedForeignId('previous_roadmap_id');
            $table->dropColumn([
                'target_role', 'rationale', 'generation_version', 'generated_at', 'refreshed_at',
                'progress_at_generation', 'generation_input_snapshot',
            ]);
        });
    }
};
