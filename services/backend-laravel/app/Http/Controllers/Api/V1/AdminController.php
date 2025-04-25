<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\HttpResponse;
use App\Http\Resources\CommentResource;
use App\Http\Resources\FilterLogResource;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\FilterLog;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
        ];
    }

    public function getAllLogs()
    {
        $filteredLogs = FilterLog::paginate(config('app.pagination.limit'));
        return HttpResponse::paginate(FilterLogResource::collection($filteredLogs), 'Filtered logs retrieved successfully.');
    }
    public function getFilteredPosts()
    {
        $filteredPosts = Post::where('status', 'flagged')->paginate(config('app.pagination.limit'));
        return HttpResponse::paginate(PostResource::collection($filteredPosts), 'Filtered posts retrieved successfully.');
    }

    public function getFilteredComments()
    {
        $filteredComments = Comment::where('status', 'flagged')->paginate(config('app.pagination.limit'));
        return HttpResponse::paginate(CommentResource::collection($filteredComments), 'Filtered comments retrieved successfully.');
    }

    public function approveOrRejectPost(Request $request, Post $post)
    {
        //
    }

    public function approveOrRejectComment(Request $request, Comment $comment)
    {
        //
    }
}
