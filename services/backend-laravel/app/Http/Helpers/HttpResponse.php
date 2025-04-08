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
}
