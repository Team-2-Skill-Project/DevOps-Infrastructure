<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('candidate_profile_id')->constrained()->cascadeOnDelete();
            $table->string('language');
            $table->string('proficiency_level')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
