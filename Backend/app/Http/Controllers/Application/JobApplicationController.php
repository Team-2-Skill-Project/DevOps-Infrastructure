<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreJobApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\JobApplicationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class JobApplicationController extends Controller
{
    use ApiResponse;

    protected JobApplicationService $applicationService;

    public function __construct(JobApplicationService $applicationService)
    {
        $this->applicationService = $applicationService;
    }

    /**
     * Display a listing of the job applications for the authenticated user, with optional filtering by status and search by job title.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Application::query()->with(['job.company', 'candidateProfile.user', 'histories']);

        if ($user->role === 'candidate' || $user->candidateProfile) {
            $query->where('candidate_profile_id', $user->candidateProfile->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('job', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest()->paginate(10);

        return ApplicationResource::collection($applications);
    }

    /**
     * Store a new job application and log the initial status in the history table.
     */
    public function store(StoreJobApplicationRequest $request): Response|JsonResponse
    {
        $candidateProfileId = $request->user()->candidateProfile->id;

        $application = $this->applicationService->createApplication($candidateProfileId, $request->validated());

        $application->load(['job.company', 'candidateProfile.user', 'histories']);

        return (new ApplicationResource($application))
            ->additional([
                'status' => 'success',
                'message' => __('application.submitted_success'),
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show the details of a specific job application.
     */
    public function show(Application $application): ApplicationResource
    {
        $application->load(['job.company', 'histories.changer', 'candidateProfile.user']);

        return new ApplicationResource($application);
    }

    /**
     * Update the status of a job application (For Admin or Company Only).
     */
    public function updateStatus(Request $request, Application $application): ApplicationResource|JsonResponse
    {
        $user = $request->user();

        if ($user->role === 'candidate' && $application->candidate_profile_id !== $user->candidateProfile?->id) {
            return $this->errorResponse(__('application.unauthorized'), 403);
        }

        $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $updatedApplication = $this->applicationService->updateStatus(
            $application,
            $request->status,
            $request->notes
        );

        $updatedApplication->load(['job.company', 'candidateProfile.user', 'histories']);

        return (new ApplicationResource($updatedApplication))
            ->additional([
                'status' => 'success',
                'message' => __('application.status_updated_success'),
            ]);
    }

    /**
     * Pull request to withdraw the application (For Candidate Only).
     */
    public function withdraw(Application $application): ApplicationResource|JsonResponse
    {
        if ($application->candidate_profile_id !== auth()->user()->candidateProfile->id) {
            return $this->errorResponse(__('application.unauthorized_action'), 403);
        }

        $updatedApplication = $this->applicationService->updateStatus(
            $application,
            'withdrawn',
            'The candidate withdrew the application.'
        );

        $updatedApplication->load(['job.company', 'candidateProfile.user', 'histories']);

        return (new ApplicationResource($updatedApplication))
            ->additional([
                'status' => 'success',
                'message' => __('application.withdrawn_success'),
            ]);
    }
}
