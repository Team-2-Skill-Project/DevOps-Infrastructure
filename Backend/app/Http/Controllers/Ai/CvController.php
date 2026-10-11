<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Services\Ai\CvExtractionService;
use App\Services\CvService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CvController extends Controller
{
    public function __construct(
        protected CvService $cvService,
        protected CvExtractionService $cvExtractionService,
    ) {}

    public function uploadAndExtract(Request $request): JsonResponse
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $profile = $request->user()->candidateProfile;

        if ($profile === null) {
            return response()->json([
                'success' => false,
                'message' => __('cv.candidate_profile_not_found'),
            ], 422);
        }

        /** @var UploadedFile $file */
        $file = $request->file('cv');
        $cvDocument = $this->cvService->uploadOrReplaceCv($profile, $file);
        $result = $this->cvExtractionService->extractAndPersist($cvDocument);

        return response()->json([
            'success' => true,
            'message' => __('cv.uploaded_success'),
            'data' => $result,
        ]);
    }
}
