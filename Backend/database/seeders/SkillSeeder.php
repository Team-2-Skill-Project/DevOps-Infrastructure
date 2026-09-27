<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Laravel', 'normalized_name' => 'laravel', 'category' => 'Backend'],
            ['name' => 'PostgreSQL', 'normalized_name' => 'postgresql', 'category' => 'Database'],
        ] as $skill) {
            Skill::query()->firstOrCreate(['name' => $skill['name']], $skill);
        }
    }
}
