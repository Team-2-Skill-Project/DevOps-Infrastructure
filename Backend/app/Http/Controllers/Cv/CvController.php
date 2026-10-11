<?php

namespace App\Http\Controllers\Cv;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cv\UploadCvRequest;
use App\Http\Requests\Cv\VerifyCvExtractionRequest;
use App\Http\Resources\CvDocumentResource;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Services\CvService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CvController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function __construct(protected CvService $cvService) {}

    /**
     * Upload a new CV document for the authenticated user, replacing any existing CV.
     */
    public function store(UploadCvRequest $request): JsonResponse
    {
        $profile = $request->user()->candidateProfile;

        if (! $profile) {
            return $this->errorResponse('cv.candidate_profile_not_found', 404);
        }

        $cvDocument = $this->cvService->uploadOrReplaceCv($profile, $request->file('cv'));
        $cvDocument->load('extractions');

        return (new CvDocumentResource($cvDocument))
            ->additional([
                'status' => 'success',
                'message' => __('cv.uploaded'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified CV document along with its extractions.
     */
    public function show(Request $request, CvDocument $cvDocument): CvDocumentResource
    {
        $this->authorize('view', $cvDocument);
        $cvDocument->load('extractions');

        return new CvDocumentResource($cvDocument);
    }

    /**
     * Display a listing of the CV documents for the authenticated user, ordered by version.
     */
    public function history(Request $request): AnonymousResourceCollection
    {
        $profile = $request->user()->candidateProfile;

        $history = $profile === null
            ? collect()
            : CvDocument::query()
                ->where('candidate_profile_id', $profile->id)
                ->orderByDesc('version')
                ->get();

        return CvDocumentResource::collection($history);
    }

    /**
     * Retry processing a CV document that previously failed.
     */
    public function retry(Request $request, CvDocument $cvDocument): CvDocumentResource
    {
        $this->authorize('update', $cvDocument);

        $updatedCv = $this->cvService->retryProcessing($cvDocument);
        $updatedCv->load('extractions');

        return (new CvDocumentResource($updatedCv))
            ->additional([
                'status' => 'success',
                'message' => __('cv.processing_retried'),
            ]);
    }

    /**
     * Verify the extracted data from a CV extraction and sync it to the candidate profile.
     */
    public function verify(VerifyCvExtractionRequest $request, CvExtraction $extraction): JsonResponse
    {
        $profile = $request->user()->candidateProfile;

        $this->cvService->verifyAndSyncExtractedData($profile, $extraction, $request->validated());

        return $this->successResponse(null, 'cv.extraction_verified');
    }
}
