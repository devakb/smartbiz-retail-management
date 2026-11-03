<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(string $message = 'Success.', mixed $data = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => (object) [],
        ], $status);
    }

    public static function error(string $message = 'Error.', mixed $errors = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => (object) [],
            'errors' => $errors,
        ], $status);
    }
}
