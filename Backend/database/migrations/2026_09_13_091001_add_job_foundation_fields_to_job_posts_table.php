<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->string('job_type')->default('job')->after('title');
            $table->boolean('is_active')->default(true)->after('status');
            $table->string('application_method')->nullable()->after('application_url');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table): void {
            $table->dropColumn(['job_type', 'is_active', 'application_method']);
        });
    }
};
