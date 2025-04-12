<?php

namespace Tests\Feature;

use App\Enums\StatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use App\Services\AnalyzeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_comments(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $post = Post::factory()->for($user)->create();

        $approvedComment = Comment::factory()->create([
            'post_id' => $post->id,
            'status' => StatusEnum::APPROVED->value,
            'user_id' => $user->id,
        ]);

        $unapprovedComment = Comment::factory()->create([
            'post_id' => $post->id,
            'status' => StatusEnum::PENDING->value,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/v1/posts/{$post->id}/comments");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'content' => $approvedComment->content,
            'status' => StatusEnum::APPROVED->value,
        ]);
        $response->assertJsonMissing([
            'content' => $unapprovedComment->content,
            'status' => StatusEnum::PENDING->value,
        ]);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => ['content', 'status', 'date', 'post', 'creator']
            ],
            'meta',
            'links'
        ]);
    }

    public function test_store_creates_comment_and_flags(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $commentData = [
            'content' => 'This is a comment.',
            'ai_model' => 'toxic-bert',
        ];

        $this->mock(AnalyzeService::class, function ($mock) {
            $mock->shouldReceive('analyzeContent')
                ->once()
                ->andReturn([
                    'is_flagged' => true,
                    'reason' => 'Toxic content detected',
                    'confidence' => 0.95,
                ]);
        });

        $response = $this->postJson("/api/v1/posts/{$post->id}/comments", $commentData);

        $response->assertStatus(201);

        $response->assertJson([
            'status' => 201,
            'message' => 'Comment created successfully',
        ]);

        $this->assertDatabaseHas('comments', [
            'content' => $commentData['content'],
            'status' => StatusEnum::FLAGGED->value,
            'post_id' => $post->id,
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('filter_logs', [
            'content_id' => 1,
            'reason' => 'Toxic content detected',
            'confidence' => 0.95,
        ]);
    }

    public function test_update_edits_comment_with_valid_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $comment = $post->comments()->create([
            'content' => 'original comment',
            'user_id' => $user->id,
            'status' => StatusEnum::PENDING->value,
        ]);

        $commentData = [
            'content' => 'updated comment content.',
            'ai_model' => 'toxic-bert',
        ];

        $this->mock(AnalyzeService::class, function ($mock) {
            $mock->shouldReceive('analyzeContent')
                ->once()
                ->andReturn([
                    'is_flagged' => true,
                    'reason' => 'Toxic content detected',
                    'confidence' => 0.95,
                ]);
        });

        $response = $this->putJson("/api/v1/posts/{$post->id}/comments/{$comment->id}", $commentData);

        $response->assertStatus(200);

        $response->assertJson([
            'status' => 200,
            'message' => 'Comment updated successfully',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'content' => $commentData['content'],
            'status' => StatusEnum::FLAGGED->value,
        ]);

        $this->assertDatabaseHas('filter_logs', [
            'content_id' => $comment->id,
            'reason' => 'Toxic content detected',
            'confidence' => 0.95,
        ]);
    }

    public function test_destroy_soft_deletes_comment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $comment = $post->comments()->create([
            'content' => 'this is a comment',
            'user_id' => $user->id,
            'status' => StatusEnum::PENDING->value,
        ]);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}/comments/{$comment->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'message' => 'Comment deleted successfully',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => StatusEnum::DELETED->value,
        ]);

        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);

        $this->assertDatabaseMissing('filter_logs', [
            'comment_id' => $comment->id,
        ]);
    }

    public function test_restore_recovers_deleted_comment(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
        ]);

        $comment = $post->comments()->create([
            'content' => 'this is a deleted comment',
            'user_id' => $user->id,
            'status' => StatusEnum::DELETED->value,
        ]);

        $comment->update(['status' => StatusEnum::PENDING]);
        $comment->delete();

        $response = $this->patchJson("/api/v1/posts/{$post->id}/comments/{$comment->id}/restore");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'message' => 'Comment restored successfully.',
        ]);

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
            'deleted_at' => $comment->deleted_at,
        ]);
    }

}
