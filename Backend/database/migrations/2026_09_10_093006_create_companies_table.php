<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('normalized_name')->unique();

            $table->string('website_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('logo_url')->nullable();

            $table->string('industry')->nullable();

            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);

            $table
                ->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
