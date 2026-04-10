<?php

namespace App\Support\Responses;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    public static function success(mixed $data = null, ?string $message = null, int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $status);
    }

    public static function created(mixed $data = null, ?string $message = null): JsonResponse
    {
        return self::success($data, $message, Response::HTTP_CREATED);
    }

    public static function error(string $message, array $errors = [], int $status = Response::HTTP_BAD_REQUEST): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, mixed $data = null, ?string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data ?? $paginator->items(),
            'errors' => null,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $paginator->path(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
