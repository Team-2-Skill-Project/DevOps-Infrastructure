<?php

namespace Database\Seeders;

use App\Models\JobSource;
use Illuminate\Database\Seeder;

class JobSourceSeeder extends Seeder
{
    public function run(): void
    {
        JobSource::query()->firstOrCreate(['slug' => 'manual'], [
            'name' => __('job_sources.manual_name'),
            'source_type' => 'manual',
            'collection_method' => 'manual',
        ]);
    }
}
