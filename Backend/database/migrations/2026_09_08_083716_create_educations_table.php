<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('education_level')->nullable();
            $table->string('institution')->nullable();
            $table->string('field_of_study')->nullable();
            $table->string('degree')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->string('grade')->nullable();
            $table->text('description')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
