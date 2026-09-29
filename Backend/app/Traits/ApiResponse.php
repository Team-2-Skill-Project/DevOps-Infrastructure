<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * @param  mixed  $data
     * @param  array<string,mixed>  $replace
     */
    protected function successResponse($data, ?string $messageKey = null, array $replace = [], int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $messageKey ? __($messageKey, $replace) : null,
            'data' => $data,
        ], $code);
    }

    /**
     * @param  array<string,mixed>  $replace
     * @param  mixed|null  $errors
     */
    protected function errorResponse(string $messageKey, int $code, array $replace = [], $errors = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => __($messageKey, $replace),
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
