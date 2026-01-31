<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success JSON response.
     *
     * @param  array<string, mixed>  $data
     */
    protected function success(array $data = [], string $message = 'Success.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Return a created (201) JSON response.
     *
     * @param  array<string, mixed>  $data
     */
    protected function created(array $data = [], string $message = 'Created successfully.'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    /**
     * Return an error JSON response.
     *
     * @param  array<string, mixed>  $errors  Additional error details (e.g. otp_attempts, remaining_minutes).
     */
    protected function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    /**
     * Return a 404 Not Found response.
     */
    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error($message, 404);
    }

    /**
     * Return a 401 Unauthorized response.
     */
    protected function unauthorized(string $message = 'Unauthorized.'): JsonResponse
    {
        return $this->error($message, 401);
    }

    /**
     * Return a 403 Forbidden response.
     */
    protected function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->error($message, 403);
    }

    /**
     * Return a 422 Unprocessable Entity response (e.g. validation / lock).
     *
     * @param  array<string, mixed>  $errors
     */
    protected function unprocessable(string $message, array $errors = []): JsonResponse
    {
        return $this->error($message, 422, $errors);
    }

    /**
     * Return a 423 Locked response (e.g. OTP / rate limit locked).
     *
     * @param  array<string, mixed>  $errors
     */
    protected function locked(string $message, array $errors = []): JsonResponse
    {
        return $this->error($message, 423, $errors);
    }
}
