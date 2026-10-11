<?php

namespace App\Observers;

use App\Models\CvExtraction;
use App\Services\AiReviewService;

class CvExtractionObserver
{
    public function saved(CvExtraction $cvExtraction): void
    {
        app(AiReviewService::class)->createForCvExtraction($cvExtraction);
    }
}
