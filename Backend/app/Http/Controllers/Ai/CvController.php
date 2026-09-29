<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\CvDocument;
use App\Services\Ai\CvExtractionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CvController extends Controller
{
    protected CvExtractionService $cvExtractionService;

    public function __construct(CvExtractionService $cvExtractionService)
    {
        $this->cvExtractionService = $cvExtractionService;
    }

    public function uploadAndExtract(Request $request): JsonResponse
    {
        $request->validate([
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $user = $request->user();
        $file = $request->file('cv');

        $path = $file->store('cvs', 'public');

        $cvDocument = CvDocument::create([
            'user_id' => $user->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'parsing_status' => 'pending',
        ]);

        $result = $this->cvExtractionService->extractAndPersist($cvDocument);

        return response()->json([
            'success' => true,
            'message' => __('application.cv_extracted_successfully') ?: 'تم رفع وتحليل السيرة الذاتية بنجاح.',
            'data' => $result,
        ]);
    }
}
