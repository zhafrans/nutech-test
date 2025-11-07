<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Enums\ResponseCode;

class Authenticate extends Middleware
{
    /**
     * Handle unauthenticated requests.
     */
    protected function redirectTo($request)
    {
        // Kalau API request, jangan redirect ke login
        if ($request->expectsJson() || $request->is('api/*')) {
            return null; // biar lempar exception ke ResponseHelper
        }
    }

    /**
     * Override handle untuk kirim JSON Unauthorized jika API.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (\Illuminate\Auth\AuthenticationException $e) {
            // Jika API, balas JSON
            if ($request->is('api/*')) {
                return ResponseHelper::generate(ResponseCode::Unauthorized);
            }

            throw $e;
        }

        return $next($request);
    }
}
