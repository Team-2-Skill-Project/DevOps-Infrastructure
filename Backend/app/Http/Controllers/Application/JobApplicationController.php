<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Http\Requests\Application\StoreJobApplicationRequest;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Traits\ApiResponse;
use App\Services\JobApplicationService;
use Illuminate\Http\Request;

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
     *
     */
    public function index(Request $request)
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
            $query->whereHas('job', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        $applications = $query->latest()->paginate(10);

        return ApplicationResource::collection($applications);
    }

    /**
     * 2. store a new job application and log the initial status in the history table.
     */
    public function store(StoreJobApplicationRequest $request)
    {
        $candidateProfileId = $request->user()->candidateProfile->id;

        $application = $this->applicationService->createApplication($candidateProfileId, $request->validated());

        $application->load(['job.company', 'candidateProfile.user', 'histories']);

        return (new ApplicationResource($application))
            ->additional([
                'status' => 'success',
                'message' => 'The request has been submitted successfully.'
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * 3. show the details of a specific job application, including related job and company information, status history, and candidate profile with user information.
     */
    public function show(Application $application)
    {
        $application->load(['job.company', 'histories.changer', 'candidateProfile.user']);

        return new ApplicationResource($application);
    }

    /**
     * 4. update the status of a job application and log the change in the history table (Update Status - For Admin or Company Only)
     */
    public function updateStatus(Request $request, Application $application)
    {
        $user = $request->user();

        if ($user->role === 'candidate' && $application->candidate_profile_id !== $user->candidateProfile?->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $request->validate([
            'status' => ['required', 'string'],
            'notes' => ['nullable', 'string', 'max:500']
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
                'message' => 'The application status has been successfully updated.'
            ]);
    }

    /**
     * 5. pull request to withdraw the application (Withdraw Application - For Candidate Only)
     */
    public function withdraw(Application $application)
    {
        if ($application->candidate_profile_id !== auth()->user()->candidateProfile->id) {
            return $this->errorResponse('You are not authorized to perform these operations.', 403);
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
                'message' => 'The application has been successfully withdrawn.'
            ]);
    }
}
