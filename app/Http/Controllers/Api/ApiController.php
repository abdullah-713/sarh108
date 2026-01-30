<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    /**
     * Success response.
     */
    protected function success($data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Operation successful',
        ], $code);
    }

    /**
     * Error response.
     */
    protected function error($message = 'An error occurred', $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'error_code' => $code,
        ], $code);
    }

    /**
     * Paginated response.
     */
    protected function paginate($paginator, $message = 'Data retrieved successfully')
    {
        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'message' => $message,
        ]);
    }
}
