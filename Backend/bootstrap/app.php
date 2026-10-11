<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->prepend(SetLocale::class);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => __('auth.unauthenticated')], 401);
            }

            return null;
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request): ?JsonResponse {
            if ($request->is('api/cv/*')) {
                return response()->json(['message' => __('cv.forbidden')], 403);
            }

            if ($request->is('api/applications', 'api/applications/*')) {
                return response()->json(['message' => __('application.unauthorized_action')], 403);
            }

            if ($request->is('api/saved-jobs', 'api/saved-jobs/*', 'api/jobs/*/save')) {
                return response()->json(['message' => __('saved_job.forbidden')], 403);
            }

            if ($request->is('api/candidate/skills', 'api/candidate/skills/*', 'api/skills/search', 'api/admin/skills', 'api/admin/skills/*')) {
                return response()->json(['message' => __('skill.forbidden')], 403);
            }

            if ($request->is('api/admin/companies', 'api/admin/companies/*')) {
                return response()->json(['message' => __('company.forbidden')], 403);
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request): ?JsonResponse {
            if ($request->is('api/admin/ai-review-items', 'api/admin/ai-review-items/*')) {
                return response()->json(['message' => __('ai_review.not_found')], 404);
            }

            if ($request->is('api/cv/extractions/*')) {
                return response()->json(['message' => __('cv.extraction_not_found')], 404);
            }

            if ($request->is('api/cv/status/*') || $request->is('api/cv/retry/*')) {
                return response()->json(['message' => __('cv.document_not_found')], 404);
            }

            if ($request->is('api/applications', 'api/applications/*')) {
                return response()->json(['message' => __('application.not_found')], 404);
            }

            if ($request->is('api/saved-jobs', 'api/saved-jobs/*', 'api/jobs/*/save')) {
                return response()->json(['message' => __('saved_job.not_found')], 404);
            }

            if ($request->is('api/candidate/skills', 'api/candidate/skills/*', 'api/skills/search', 'api/admin/skills', 'api/admin/skills/*')) {
                return response()->json(['message' => __('skill.not_found')], 404);
            }

            if ($request->is('api/admin/companies', 'api/admin/companies/*')) {
                return response()->json(['message' => __('company.not_found')], 404);
            }

            return null;
        });

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request): ?JsonResponse {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => __('auth.too_many_requests')], 429, $exception->getHeaders());
            }

            return null;
        });
    })->create();
