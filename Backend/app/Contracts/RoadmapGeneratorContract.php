<?php

namespace App\Contracts;

interface RoadmapGeneratorContract
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function generate(array $input): array;

    public function version(): string;
}
