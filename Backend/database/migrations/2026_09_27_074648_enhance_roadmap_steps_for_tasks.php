<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table): void {
            $table->foreignId('roadmap_milestone_id')->nullable()->after('roadmap_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('task_order')->nullable()->after('step_order');
            $table->timestamp('completed_at')->nullable()->after('status');
            $table->text('evidence')->nullable()->after('completed_at');
            $table->text('notes')->nullable()->after('evidence');
            $table->json('metadata')->nullable()->after('resources');

            $table->unique(['roadmap_milestone_id', 'task_order']);
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table): void {
            $table->dropUnique(['roadmap_milestone_id', 'task_order']);
            $table->dropConstrainedForeignId('roadmap_milestone_id');
            $table->dropColumn(['task_order', 'completed_at', 'evidence', 'notes', 'metadata']);
        });
    }
};
