<?php

namespace App\Http\Helpers;

class HttpResponse
{
    public static function sendResponse($data = [], $message = [], $status = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function paginate($data = [], $message = [], $status = 200)
    {
        $pagination = $data->resource->toArray();

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $pagination['data'],
            'meta' => [
                'current_page' => $pagination['current_page'],
                'last_page' => $pagination['last_page'],
                'per_page' => $pagination['per_page'],
                'total' => $pagination['total'],
                'next_page_url' => $pagination['next_page_url'],
                'prev_page_url' => $pagination['prev_page_url'],
            ],
            'links' => [
                'first' => $pagination['first_page_url'],
                'last' => $pagination['last_page_url'],
                'next' => $pagination['next_page_url'],
                'prev' => $pagination['prev_page_url'],
            ]
        ], $status);
    }
}
