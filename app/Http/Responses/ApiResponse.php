<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Return a success JSON response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @return JsonResponse
     */
    public static function success($data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = ['success' => true];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
        }

        return response()->json($response, $status);
    }

    /**
     * Return an error JSON response
     *
     * @param string $message
     * @param int $status
     * @param array $errors
     * @return JsonResponse
     */
    public static function error(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        $response = [
            'success' => false,
            'error' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors
     * @param string $message
     * @return JsonResponse
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, 422, $errors);
    }
}
