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
        Schema::create('ai_review_items', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 50);
            $table->unsignedBigInteger('entity_id');
            $table->string('operation', 50);
            $table->json('proposed_value');
            $table->json('canonical_value')->nullable();
            $table->string('trigger_reason', 50);
            $table->decimal('confidence', 5, 4)->nullable();
            $table->string('source', 191)->nullable();
            $table->char('payload_hash', 64);
            $table->string('status', 30)->default('pending');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('decision_reason')->nullable();
            $table->text('reviewer_notes')->nullable();
            $table->json('corrected_value')->nullable();
            $table->timestamps();

            $table->foreign('entity_id')->references('id')->on('cv_extractions')->cascadeOnDelete();
            $table->unique(['entity_type', 'entity_id', 'operation', 'payload_hash'], 'ai_review_items_result_unique');
            $table->index(['status', 'created_at', 'id']);
            $table->index(['entity_type', 'status']);
            $table->index(['trigger_reason', 'status']);
            $table->index(['reviewer_id', 'status']);
            $table->index('confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_review_items');
    }
};
