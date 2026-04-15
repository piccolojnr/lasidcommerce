<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ApplyStorefrontSessionConfig;
use App\Http\Middleware\EnsureStorefrontCustomer;
use App\Http\Middleware\StorefrontVerifyCsrfToken;
use App\Support\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            \Illuminate\Support\Facades\Route::middleware('web')
                ->group(__DIR__.'/../routes/settings.php');

            \Illuminate\Support\Facades\Route::middleware('api')
                ->group(__DIR__.'/../routes/webhooks.php');

            \Illuminate\Support\Facades\Route::middleware('storefront')
                ->prefix('api/v1')
                ->name('api.v1.')
                ->group(__DIR__.'/../routes/storefront.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', env('STOREFRONT_CSRF_COOKIE', 'XSRF-STOREFRONT-TOKEN')]);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->group('storefront', [
            ApplyStorefrontSessionConfig::class,
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            StorefrontVerifyCsrfToken::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'ensure.storefront.customer' => EnsureStorefrontCustomer::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, $request) {
            if ($request->is('api/v1/*')) {
                return ApiResponse::error('Unauthenticated.', [], Response::HTTP_UNAUTHORIZED);
            }
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if ($request->is('api/v1/*')) {
                return ApiResponse::error($exception->getMessage() ?: 'This action is unauthorized.', [], Response::HTTP_FORBIDDEN);
            }
        });

        $exceptions->render(function (ValidationException $exception, $request) {
            if ($request->is('api/v1/*')) {
                return ApiResponse::error('The given data was invalid.', $exception->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        });

        $exceptions->render(function (TokenMismatchException $exception, $request) {
            if ($request->is('api/v1/*')) {
                return ApiResponse::error('CSRF token mismatch.', [], Response::HTTP_CONFLICT);
            }
        });
    })->create();
