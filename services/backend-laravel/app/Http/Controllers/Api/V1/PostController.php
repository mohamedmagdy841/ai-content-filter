<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\StatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Helpers\HttpResponse;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\AnalyzeService;


class PostController extends Controller
{
    protected $analyzeService;

    public function __construct(AnalyzeService $analyzeService)
    {
        $this->analyzeService = $analyzeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with(['user','tags', 'images', 'comments' => function ($query) {
            $query->approved();
        }])->approved()->latest()->paginate(config('app.pagination.limit'));

        if ($posts->isEmpty()) {
            return HttpResponse::sendResponse([], 'No posts found.', 404);
        }

        return HttpResponse::paginate(PostResource::collection($posts), 'Posts retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $data = $request->validated();
        $data["user_id"] = auth()->id();
        $status = StatusEnum::PENDING->value;

        $response = $this->analyzeService->analyzeContent($data['content'], $data['ai_model'], $data['title']);

        if ($response) {
            if ($response["is_flagged"]){
                $status = StatusEnum::FLAGGED->value;
            } else {
                $status = StatusEnum::APPROVED->value;
            }
        }

        $data["status"] = $status;

        $post = Post::create($data);

        if ($status === StatusEnum::FLAGGED->value) {
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
        $post = Post::with(['user','tags', 'images', 'comments' => function ($query) {
            $query->approved();
        }])->approved()->find($id);

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

        $data = $request->validated();

        $status = StatusEnum::PENDING->value;

        $response = $this->analyzeService->analyzeContent($data['content'], $data['ai_model'], $data['title']);

        if ($response) {
            if ($response["is_flagged"]){
                $status = StatusEnum::FLAGGED->value;
            } else {
                $status = StatusEnum::APPROVED->value;
            }
        }

        $data["status"] = $status;

        $post->update($data);

        if($status === StatusEnum::FLAGGED->value)
        {
            $post->filterLogs()->updateOrCreate([], [
                'reason' => $response["reason"],
                'confidence' => $response["score"] ?? null,
            ]);
        } else {
            $post->filterLogs()->delete();
        }

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
        $post->update(['status' => StatusEnum::DELETED]);
        $post->delete();
        $post->filterLogs()->delete();

        return HttpResponse::sendResponse([], 'Post deleted successfully.');
    }

    public function restore(string $id)
    {
        $post = Post::withTrashed()->find($id);
        if (!$post) {
            return HttpResponse::sendResponse([], 'Post not found.', 404);
        }
        if ($post->user_id != auth()->user()->id) {
            return HttpResponse::sendResponse([], 'You are not allowed to restore this post.', 403);
        }

        if (!$post->trashed()) {
            return HttpResponse::sendResponse([], 'This post is not deleted', 400);
        }

        $post->update(['status' => StatusEnum::PENDING]);
        $post->restore();

        return HttpResponse::sendResponse(new PostResource($post), 'Post restored successfully.');
    }
}
