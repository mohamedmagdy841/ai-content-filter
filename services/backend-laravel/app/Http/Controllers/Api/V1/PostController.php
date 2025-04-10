<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Helpers\HttpResponse;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\FilterLog;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['user', 'comments'])->latest()->get();
        if ($posts->isEmpty()) {
            return HttpResponse::sendResponse([], 'No posts found.', 404);
        }
        return HttpResponse::sendResponse(PostResource::collection($posts), 'Posts retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $data["user_id"] = auth()->id();

        $response = Http::post('http://localhost:8080/analyze', [
            'title' => $data['title'],
            'content' => $data['content'],
            'ai_model' => $data['ai_model'],
        ])->json();

        if($response["is_flagged"])
        {
            $data["status"] = "flagged";
        }

        $post = Post::create($data);

        if ($response["is_flagged"]) {
            $post->filterLogs()->create([
                'reason' => $response["reason"],
                'confidence' => $response["score"] ?? null,
            ]);
        }

        return HttpResponse::sendResponse(new PostResource($post), 'Post created successfully.', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $post = Post::with(['user', 'comments'])->find($id);
        if (!$post) {
            return HttpResponse::sendResponse([], 'Post not found.', 404);
        }

        return HttpResponse::sendResponse(new PostResource($post), 'Post retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return HttpResponse::sendResponse([], 'Post not found.', 404);
        }
        if ($post->user_id != $request->user()->id) {
            return HttpResponse::sendResponse([], 'You are not allowed to update this post.', 403);
        }

        $post->update($request->validated());
        return HttpResponse::sendResponse(new PostResource($post), 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return HttpResponse::sendResponse([], 'Post not found.', 404);
        }
        if ($post->user_id != auth()->user()->id) {
            return HttpResponse::sendResponse([], 'You are not allowed to delete this post.', 403);
        }

        $post->delete();
        return HttpResponse::sendResponse([], 'Post deleted successfully.');
    }
}
