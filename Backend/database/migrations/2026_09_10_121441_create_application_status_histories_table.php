<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('application_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();

            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->text('notes')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('application_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_status_histories');
    }
};
