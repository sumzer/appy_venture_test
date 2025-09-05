<?php

namespace App\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success(array $data = [], int $status = 200, array $headers = []): JsonResponse
    {
        return response()->json(['data' => $data], $status, $headers);
    }

    public static function item($model, int $status = 200, array $headers = []): JsonResponse
    {
        return self::success($model?->toArray() ?? [], $status, $headers);
    }

    public static function paginated(LengthAwarePaginator $p): JsonResponse
    {
        $meta = [
            'total' => $p->total(),
            'page' => $p->currentPage(),
            'per_page' => $p->perPage(),
        ];

        $links = [
            'self' => request()->fullUrlWithQuery(['page' => $p->currentPage()]),
            'next' => $p->hasMorePages() ? $p->appends(request()->query())->nextPageUrl() : null,
            'prev' => $p->currentPage() > 1 ? $p->appends(request()->query())->previousPageUrl() : null,
        ];

        return response()->json([
            'data' => $p->items(),
            'meta' => $meta,
            'links' => $links,
        ]);
    }

    public static function error(string $code, string $message, array $details = [], int $status = 400): JsonResponse
    {
        return response()->json([
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details ?: null,
            ],
        ], $status);
    }
}
