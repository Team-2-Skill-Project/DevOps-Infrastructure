<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_verified_at');
        });

        Schema::table('educations', function (Blueprint $table) {
            $table->string('institution')->nullable(false)->change();
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropUnique('skills_name_unique');
            $table->dropIndex('skills_normalized_name_index');

            $table->unique('normalized_name');
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->string('proficiency_level')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('phone_verified_at')->nullable();
        });

        Schema::table('educations', function (Blueprint $table) {
            $table->string('institution')->nullable()->change();
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropUnique(['normalized_name']);

            $table->unique('name');
            $table->index('normalized_name');
        });

        Schema::table('languages', function (Blueprint $table) {
            $table->string('proficiency_level')->nullable()->change();
        });
    }
};
