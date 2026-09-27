<?php

namespace App\Services;

use App\Models\RawJob;

interface JobDetailExtractor
{
    public function extract(RawJob $rawJob): JobExtractionResult;
}
