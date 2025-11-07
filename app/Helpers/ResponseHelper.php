<?php

namespace App\Helpers;

use App\Enums\ResponseCode;
use Illuminate\Http\JsonResponse;

class ResponseHelper
{
    public static function generate(
        ResponseCode $responseCode,
        array $data = [],
        string $message = null
    ): JsonResponse {
        return response()->json(
            data: array_merge([
                'response_code' => $responseCode->value,
                'response_message' => $message ?? $responseCode->getMessage(),
            ], $data),
            status: (int) substr($responseCode->value, 0, 3)
        );
    }

    public static function paginate(
        array $items,
        int $currentPage,
        int $lastPage,
        int $perPage,
        int $total,
        array $data = []
    ): JsonResponse {
        return self::generate(ResponseCode::Ok, array_merge($data, [
            'items' => $items,
            'currentPage' => max(1, (int) $currentPage),
            'lastPage' => max(1, (int) $lastPage),
            'perPage' => (int) $perPage,
            'total' => (int) $total
        ]));
    }
}
