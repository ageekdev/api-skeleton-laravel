<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as Http;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->wantsJson() || $request->is('api/*')) {

            switch (get_class($e)) {
                case ModelNotFoundException::class:

                    return Response::error([
                        'message' => class_basename($e->getModel()).' Not Found',
                    ], Http::HTTP_NOT_FOUND);

                case NotFoundHttpException::class:

                    return Response::error([
                        'message' => 'Requested url not found',
                    ], Http::HTTP_NOT_FOUND);

                case ValidationException::class:

                    return Response::error([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                    ], $e->status);

                case AuthorizationException::class:

                    return Response::error([
                        'message' => $e->getMessage(),
                    ], Http::HTTP_FORBIDDEN);

                case AuthenticationException::class:

                    return Response::error([
                        'message' => $e->getMessage(),
                    ], Http::HTTP_UNAUTHORIZED);

                case InvalidSignatureException::class:

                    if ($request->routeIs('verification.verify')) {
                        return Response::error([
                            'message' => 'Verification link expired',
                        ], $e->getStatusCode());
                    }

                    return Response::error([
                        'message' => $e->getMessage(),
                    ], $e->getStatusCode());

                default:

                    $statusCode = (int) $e->getCode();

                    if ($this->validStatusCode($statusCode)) {
                        return Response::error([
                            'message' => $e->getMessage(),
                        ], $e->getCode());
                    }

            }
        }

        return parent::render($request, $e);
    }

    /**
     * Is response valid?
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     *
     * @final
     */
    protected function validStatusCode(int $statusCode): bool
    {
        return $statusCode > 100 && $statusCode <= 600;
    }
}
