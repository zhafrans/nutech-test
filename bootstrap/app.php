<?php

use App\Enums\ResponseCode;
use App\Exceptions\DashboardValidationException;
use App\Helpers\ResponseHelper;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        using: function () {
            Route::prefix('api/v1')
                ->middleware(['api', 'auth:jwt'])
                ->group(base_path('routes/api_v1.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->renderable(function (Throwable $e) {
            if (
                $e instanceof TokenExpiredException ||
                $e instanceof TokenInvalidException ||
                $e instanceof JWTException ||
                $e instanceof AuthenticationException
            ) {
                return ResponseHelper::generate(ResponseCode::Unauthorized);
            }
            if ($e instanceof MethodNotAllowedHttpException) {
                return ResponseHelper::generate(ResponseCode::GeneralError, ['message' => $e->getMessage()]);
            }

            if ($e instanceof AccessDeniedHttpException) {
                return ResponseHelper::generate(ResponseCode::Forbidden);
            }

            if ($e instanceof DashboardValidationException) {
                return ResponseHelper::generate(ResponseCode::ValidationError, [
                    'errors' => Arr::map($e->errors(), fn($error) => $error[0])
                ]);
            }

            if ($e instanceof ValidationException) {
                if (array_key_exists('Required', array_values($e->validator->failed())[0])) {
                    $responseCode = ResponseCode::MissingMandatoryField;
                } else {
                    $responseCode = ResponseCode::InvalidFieldFormat;
                }

                return ResponseHelper::generate(
                    responseCode: $responseCode,
                    message: $responseCode->getMessage() . ' [' .  $e->validator->errors()->keys()[0] . ']'
                );
            }
        });
    })->create();
