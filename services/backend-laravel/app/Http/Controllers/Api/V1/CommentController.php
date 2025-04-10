<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\HttpResponse;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Post $post)
    {
        $comments = $post->comments()->approved()->with('user')->get();

        if($comments->isEmpty())
        {
            return HttpResponse::sendResponse([], 'No comments found', 404);
        }

        return HttpResponse::sendResponse(CommentResource::collection($comments), 'Posts retrieved successfully');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCommentRequest $request, Post $post)
    {
        $data = $request->validated();
        $data["user_id"] = auth()->id();
        $data["post_id"] = $post->id;

        $response = Http::post("http://localhost:8080/analyze", [
            'content' => $data['content'],
        ])->json();

        if($response["is_flagged"])
        {
            $data["status"] = "flagged";
        }

        $comment = $post->comments()->create($data);

        if($response["is_flagged"])
        {
            $comment->filterLogs()->create([
                'reason' => $response["reason"],
                'confidence' => $response["confidence"],
            ]);
        }

        return HttpResponse::sendResponse(new CommentResource($comment), 'Comment created successfully', 201);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCommentRequest $request, Post $post, Comment $comment)
    {
        if($comment->post_id != $post->id)
        {
            return HttpResponse::sendResponse([], 'Comment does not belong to this post', 404);
        }

        if($comment->user_id != auth()->id())
        {
            return HttpResponse::sendResponse([], 'Unauthorized', 403);
        }

        $data = $request->validated();

        $response = Http::post("http://localhost:8080/analyze", [
            'content' => $data['content'],
        ])->json();

        $data["status"] = $response["is_flagged"] ? "flagged" : "approved";

        $comment->update($data);

        if($response["is_flagged"])
        {
            $comment->filterLogs()->updateOrCreate([], [
                'reason' => $response["reason"],
                'confidence' => $response["confidence"] ?? null,
            ]);
        } else {
            $comment->filterLogs()->delete();
        }

        return HttpResponse::sendResponse(new CommentResource($comment), 'Comment updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post, Comment $comment)
    {
        if($comment->post_id != $post->id)
        {
            return HttpResponse::sendResponse([], 'Comment does not belong to this post', 404);
        }

        if($comment->user_id != auth()->id())
        {
            return HttpResponse::sendResponse([], 'Unauthorized', 403);
        }

        $comment->filterLogs()->delete();
        $comment->delete();

        return HttpResponse::sendResponse(new CommentResource($comment), 'Comment deleted successfully');
    }
}
