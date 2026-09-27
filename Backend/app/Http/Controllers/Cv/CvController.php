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
use Illuminate\Http\Request;

class CvController extends Controller
{
    use AuthorizesRequests, ApiResponse;

    public function __construct(protected CvService $cvService) {}

    /**
     * Upload a new CV document for the authenticated user, replacing any existing CV.
     */
    public function store(UploadCvRequest $request)
    {
        $profile = $request->user()->candidateProfile;

        if (! $profile) {
            return $this->errorResponse('Candidate profile not found.', 404);
        }

        $cvDocument = $this->cvService->uploadOrReplaceCv($profile, $request->file('cv'));
        $cvDocument->load('extractions');

        return (new CvDocumentResource($cvDocument))
            ->additional([
                'status' => 'success',
                'message' => 'CV uploaded successfully and queued for processing.'
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified CV document along with its extractions.
     */
    public function show(Request $request, CvDocument $cvDocument)
    {
        $this->authorize('view', $cvDocument);

        $cvDocument->load('extractions');

        return new CvDocumentResource($cvDocument);
    }

    /**
     * Display a listing of the CV documents for the authenticated user, ordered by version.
     */
    public function history(Request $request)
    {
        $profile = $request->user()->candidateProfile;

        $history = CvDocument::where('candidate_profile_id', $profile->id)
            ->orderBy('version', 'desc')
            ->get();

        return CvDocumentResource::collection($history);
    }

    /**
     * Retry processing a CV document that previously failed.
     */
    public function retry(Request $request, CvDocument $cvDocument)
    {
        $this->authorize('update', $cvDocument);

        $updatedCv = $this->cvService->retryProcessing($cvDocument);
        $updatedCv->load('extractions');

        return (new CvDocumentResource($updatedCv))
            ->additional([
                'status' => 'success',
                'message' => 'CV processing retried successfully.'
            ]);
    }

    /**
     * Verify the extracted data from a CV extraction and sync it to the candidate profile.
     */
    public function verify(VerifyCvExtractionRequest $request, CvExtraction $extraction)
    {
        $profile = $request->user()->candidateProfile;

        $this->cvService->verifyAndSyncExtractedData($profile, $extraction, $request->validated());

        return $this->successResponse(null, 'Extracted data verified and synced to profile successfully.');
    }
}
