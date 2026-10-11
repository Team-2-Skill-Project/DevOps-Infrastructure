<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_review_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_review_item_id')->constrained()->cascadeOnDelete();
            $table->string('action', 30);
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('original_proposal');
            $table->json('canonical_before')->nullable();
            $table->json('applied_value')->nullable();
            $table->text('decision_reason')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['ai_review_item_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_review_audits');
    }
};
