<?php

namespace App\Exceptions;

use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        // 422 Validation errors
        $this->renderable(function (ValidationException $e, $request) {
            return ApiResponse::error(
                'validation_failed',
                'Invalid input.',
                $e->errors(),
                422
            );
        });

        // 401 Unauthenticated
        $this->renderable(function (AuthenticationException $e, $request) {
            return ApiResponse::error(
                'unauthenticated',
                'Authentication required.',
                [],
                401
            );
        });

        // 403 Forbidden (authorization)
        $this->renderable(function (AuthorizationException $e, $request) {
            return ApiResponse::error(
                'forbidden',
                'You are not allowed to perform this action.',
                [],
                403
            );
        });

        // 404 Not found (model or route)
        $this->renderable(function (ModelNotFoundException $e, $request) {
            return ApiResponse::error(
                'not_found',
                'Resource not found.',
                [],
                404
            );
        });
        $this->renderable(function (NotFoundHttpException $e, $request) {
            return ApiResponse::error(
                'not_found',
                'Endpoint not found.',
                [],
                404
            );
        });

        // OCC / Precondition cases (412 / 428)
        $this->renderable(function (HttpException $e, $request) {
            $code = $e->getStatusCode();
            if (in_array($code, [412, 428], true)) {
                return ApiResponse::error(
                    $code === 428 ? 'precondition_required' : 'precondition_failed',
                    $e->getMessage() ?: ($code === 428 ? 'If-Match required.' : 'ETag mismatch.'),
                    [],
                    $code
                );
            }
        });

        // Production-safe fallback for unexpected errors
        $this->renderable(function (Throwable $e, $request) {
            if (app()->environment('production')) {
                return ApiResponse::error(
                    'server_error',
                    'An unexpected error occurred.',
                    [],
                    500
                );
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return ApiResponse::error(
            'unauthenticated',
            'Authentication required.',
            [],
            401
        );
    }
}
