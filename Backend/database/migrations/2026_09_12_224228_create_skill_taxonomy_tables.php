<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('skill_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('normalized_name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('skills', function (Blueprint $table): void {
                $table->foreignId('skill_category_id')->nullable()->constrained()->nullOnDelete();
            });
        });

        Schema::create('skill_aliases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->string('alias');
            $table->string('normalized_alias')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_aliases');
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('skills', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('skill_category_id');
            });
        });
        Schema::dropIfExists('skill_categories');
    }
};
