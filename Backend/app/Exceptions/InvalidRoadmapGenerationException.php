<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class InvalidRoadmapGenerationException extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => __('roadmap.generation_invalid')], 502);
    }
}
