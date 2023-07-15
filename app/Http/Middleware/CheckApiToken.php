<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\Response as Http;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function handle(Request $request, Closure $next, string $app)
    {
        $token = $request->header('x_api_token');
        if (! $token || ! data_get(config('api-tokens'), $token) || data_get(config('api-tokens'), $token) != $app) {
            return Response::error(
                ['message' => 'Invalid Api Token!'],
                Http::HTTP_UNAUTHORIZED
            );
        }

        return $next($request);
    }
}
