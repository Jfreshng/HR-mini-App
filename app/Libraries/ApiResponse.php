<?php

namespace App\Libraries;

class ApiResponse
{
    /**
     * Success response
     *
     * @param string $message
     * @param mixed $data
     * @param int $code
     * @param array $metadata
     * @return array
     */
    public static function success(string $message, $data = null, int $code = 200, array $metadata = []): array
    {
        return [
            'status' => 'success',
            'code' => $code,
            'message' => $message,
            'metadata' => $metadata,
            'data' => $data,
            'errors' => []
        ];
    }

    /**
     * Error response
     *
     * @param string $message
     * @param array|string $errors
     * @param int $code
     * @param array $metadata
     * @return array
     */
    public static function error(string $message, $errors = [], int $code = 400, array $metadata = []): array
    {
        // Ensure $errors is always an array
        if (!is_array($errors)) {
            $errors = [$errors];
        }

        return [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'metadata' => $metadata,
            'data' => null,
            'errors' => $errors
        ];
    }
}
