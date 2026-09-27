<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_aliases', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('company_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->string('alias');
            $table->string('normalized_alias')->unique();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_aliases');
    }
};
