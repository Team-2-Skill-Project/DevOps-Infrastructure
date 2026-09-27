<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobPost;
use Illuminate\Database\Seeder;

class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        if ($companies->isEmpty()) {
            return;
        }

        foreach ($companies as $company) {
            JobPost::factory(3)->create([
                'company_id' => $company->id,
                'status' => 'active',
                'published_at' => now(),
            ]);
        }
    }
}
