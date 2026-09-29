<?php

use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\IngestionRunController;
use App\Http\Controllers\Admin\JobPostController;
use App\Http\Controllers\Admin\JobSourceController;
use App\Http\Controllers\Admin\RawJobController;
use App\Http\Controllers\Admin\SkillAliasController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\SkillMergeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailPasswordResetController;
use App\Http\Controllers\Candidate\CandidateSkillController;
use App\Http\Controllers\Candidate\CareerPreferenceController;
use App\Http\Controllers\Candidate\EducationController;
use App\Http\Controllers\Candidate\ExperienceController;
use App\Http\Controllers\Candidate\ProfileController;
use App\Http\Controllers\Candidate\ProjectController;
use App\Http\Controllers\JobFeedController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SavedJobController;
use App\Http\Controllers\SkillSearchController;
use App\Http\Middleware\EnsureActiveAdmin;
use App\Http\Middleware\EnsureActiveCandidate;
use App\Http\Middleware\EnsureActiveUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1,api-login:');
Route::get('/auth/me', [AuthController::class, 'me'])->middleware('auth:api');
Route::get('/candidate/profile', [ProfileController::class, 'show'])->middleware(['auth:api', EnsureActiveCandidate::class]);
Route::patch('/candidate/profile', [ProfileController::class, 'update'])->middleware(['auth:api', EnsureActiveCandidate::class]);
Route::post('/auth/verify-email-otp', [AuthController::class, 'verifyEmailOtp'])->middleware('throttle:password-reset-verification');
Route::post('/auth/resend-email-otp', [AuthController::class, 'resendEmailOtp'])->middleware('throttle:email-verification-otp');
Route::post('/auth/forgot-password', [EmailPasswordResetController::class, 'store'])
    ->middleware('throttle:password-reset-otp');
Route::post('/auth/forgot-password/verify-otp', [EmailPasswordResetController::class, 'verifyOtp'])
    ->middleware('throttle:password-reset-verification');
Route::post('/auth/reset-password', [EmailPasswordResetController::class, 'resetPassword'])
    ->middleware('throttle:password-reset-verification');
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/candidate/career-preferences', [CareerPreferenceController::class, 'show'])->middleware(['auth:api', EnsureActiveCandidate::class]);
Route::patch('/candidate/career-preferences', [CareerPreferenceController::class, 'update'])->middleware(['auth:api', EnsureActiveCandidate::class]);

Route::middleware(['auth:api', EnsureActiveCandidate::class])->group(function (): void {
    Route::get('/candidate/educations', [EducationController::class, 'index']);
    Route::post('/candidate/educations', [EducationController::class, 'store']);
    Route::get('/candidate/educations/{education}', [EducationController::class, 'show']);
    Route::patch('/candidate/educations/{education}', [EducationController::class, 'update']);
    Route::delete('/candidate/educations/{education}', [EducationController::class, 'destroy']);
});

Route::middleware(['auth:api', EnsureActiveCandidate::class])->group(function (): void {
    Route::get('/candidate/experiences', [ExperienceController::class, 'index']);
    Route::post('/candidate/experiences', [ExperienceController::class, 'store']);
    Route::get('/candidate/experiences/{experience}', [ExperienceController::class, 'show']);
    Route::patch('/candidate/experiences/{experience}', [ExperienceController::class, 'update']);
    Route::delete('/candidate/experiences/{experience}', [ExperienceController::class, 'destroy']);
});

Route::middleware(['auth:api', EnsureActiveCandidate::class])->group(function (): void {
    Route::get('/candidate/projects', [ProjectController::class, 'index']);
    Route::post('/candidate/projects', [ProjectController::class, 'store']);
    Route::get('/candidate/projects/{project}', [ProjectController::class, 'show']);
    Route::patch('/candidate/projects/{project}', [ProjectController::class, 'update']);
    Route::delete('/candidate/projects/{project}', [ProjectController::class, 'destroy']);
});

Route::middleware(['auth:api', EnsureActiveCandidate::class])->group(function (): void {
    Route::get('/candidate/skills', [CandidateSkillController::class, 'index']);
    Route::post('/candidate/skills', [CandidateSkillController::class, 'store']);
    Route::delete('/candidate/skills/{candidateSkill}', [CandidateSkillController::class, 'destroy']);
});

Route::get('/skills/search', SkillSearchController::class)->middleware('auth:api');
Route::get('/jobs', JobFeedController::class)->middleware(['auth:api', EnsureActiveUser::class]);
Route::middleware(['auth:api', EnsureActiveCandidate::class])->group(function (): void {
    Route::post('/jobs/{jobPost}/save', [SavedJobController::class, 'store']);
    Route::delete('/jobs/{jobPost}/save', [SavedJobController::class, 'destroy']);
    Route::get('/saved-jobs', [SavedJobController::class, 'index']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
});

Route::middleware(['auth:api', EnsureActiveAdmin::class])->group(function (): void {
    Route::post('/admin/job-sources', [JobSourceController::class, 'store']);
    Route::get('/admin/job-sources', [JobSourceController::class, 'index']);
    Route::get('/admin/job-sources/{jobSource}/raw-jobs', [RawJobController::class, 'index']);
    Route::get('/admin/job-sources/{jobSource}/raw-jobs/{rawJob}', [RawJobController::class, 'show']);
    Route::get('/admin/job-sources/{jobSource}/runs', [IngestionRunController::class, 'index']);
    Route::get('/admin/job-sources/{jobSource}', [JobSourceController::class, 'show']);
    Route::patch('/admin/job-sources/{jobSource}', [JobSourceController::class, 'update']);
    Route::post('/admin/companies', [CompanyController::class, 'store']);
    Route::patch('/admin/companies/{company}', [CompanyController::class, 'update']);
    Route::post('/admin/companies/{sourceCompany}/merge', [CompanyController::class, 'merge']);
    Route::get('/admin/companies', [CompanyController::class, 'index']);
    Route::get('/admin/companies/{company}', [CompanyController::class, 'show']);
    Route::post('/admin/jobs', [JobPostController::class, 'store']);
    Route::patch('/admin/jobs/{jobPost}', [JobPostController::class, 'update']);
    Route::get('/admin/jobs/{jobPost}', [JobPostController::class, 'show']);
    Route::post('/admin/skills', [SkillController::class, 'store']);
    Route::patch('/admin/skills/{skill}', [SkillController::class, 'update']);
    Route::get('/admin/skills/{skill}/aliases', [SkillAliasController::class, 'index']);
    Route::post('/admin/skills/{skill}/aliases', [SkillAliasController::class, 'store']);
    Route::patch('/admin/skills/{skill}/aliases/{alias}', [SkillAliasController::class, 'update']);
    Route::delete('/admin/skills/{skill}/aliases/{alias}', [SkillAliasController::class, 'destroy']);
});

Route::post('/admin/skills/{sourceSkill}/merge', SkillMergeController::class)->middleware(['auth:api', EnsureActiveAdmin::class]);

require __DIR__.'/cv.php';
require __DIR__.'/applications.php';
require __DIR__.'/ai.php';
