<?php

namespace App\Helpers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class LogHelper
{
    const GENERAL = 'general';

    public static function create(string $path, string $message, array $context = []): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/' . $path . '/' . now()->format('Y-m-d_H') . '.log'),
        ])->info(
            message: $message,
            context: array_merge([
                'trace_class' => $trace[1]['class'] ?? null,
                'trace_line' => $trace[0]['line'] ?? null
            ], $context)
        );
    }

    public static function context(Throwable|Response $data): array
    {
        if ($data instanceof Throwable) {
            return [
                'throwable_class' => get_class($data),
                'throwable_message' => $data->getMessage()
            ];
        }

        return [
            'response_status' => $data->status(),
            'response_body' => $data->json() ?? $data->body()
        ];
    }
}
