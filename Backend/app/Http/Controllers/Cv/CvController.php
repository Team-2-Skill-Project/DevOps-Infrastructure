<?php

namespace App\Http\Controllers\Cv;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cv\UploadCvRequest;
use App\Http\Requests\Cv\VerifyCvExtractionRequest;
use App\Http\Resources\CvDocumentResource;
use App\Models\CvDocument;
use App\Models\CvExtraction;
use App\Services\Ai\CvExtractionService;
use App\Services\CvService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CvController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    public function __construct(
        protected CvService $cvService,
        protected CvExtractionService $cvExtractionService
    ) {}

    /**
     * Upload a new CV document, link it to candidate profile, and start AI extraction.
     */
    public function store(UploadCvRequest $request): JsonResponse
    {
        $user = $request->user();

        $profile = $user->candidateProfile()->firstOrCreate(
            ['user_id' => $user->id],
            ['full_name' => $user->name ?? 'Candidate']
        );

        $cvDocument = $this->cvService->uploadOrReplaceCv($profile, $request->file('cv'));

        try {
            $this->cvExtractionService->extractAndPersist($cvDocument);
        } catch (\Exception $e) {

            $cvDocument->update(['parsing_status' => 'failed']);
        }

        $cvDocument->load('extractions');

        return (new CvDocumentResource($cvDocument))
            ->additional([
                'status' => 'success',
                'message' => __('cv.uploaded_success') ?: 'تم رفع وتحليل السيرة الذاتية بالذكاء الاصطناعي بنجاح.',
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
    public function history(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $profile = $request->user()->candidateProfile;

        if (! $profile) {
            return $this->errorResponse('cv.profile_not_found', 404);
        }

        $history = CvDocument::where('candidate_profile_id', $profile->id)
            ->orderBy('version', 'desc')
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

        try {
            $this->cvExtractionService->extractAndPersist($updatedCv);
        } catch (\Exception $e) {
            $updatedCv->update(['parsing_status' => 'failed']);
        }

        $updatedCv->load('extractions');

        return (new CvDocumentResource($updatedCv))
            ->additional([
                'status' => 'success',
                'message' => __('cv.retried_success') ?: 'تم إعادة محاولة التحليل بنجاح.',
            ]);
    }

    /**
     * Verify the extracted data from a CV extraction and sync it to the candidate profile.
     */
    public function verify(VerifyCvExtractionRequest $request, CvExtraction $extraction): JsonResponse
    {
        $profile = $request->user()->candidateProfile;

        if (! $profile) {
            return $this->errorResponse('cv.profile_not_found', 404);
        }

        $this->cvService->verifyAndSyncExtractedData($profile, $extraction, $request->validated());

        return $this->successResponse(null, 'cv.verified_success');
    }
}
