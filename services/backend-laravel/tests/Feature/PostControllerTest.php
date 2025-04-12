<?php

namespace Tests\Feature;

use App\Enums\StatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_posts(): void
    {
        $response = $this->getJson('/api/v1/posts/');
        $response->assertNotFound();
        $response->assertJsonPath('message', 'No posts found.');

        $user = User::factory()->create();
        Post::factory()
            ->count(3)
            ->for($user)
            ->has(
                Comment::factory()->count(3)->state(['status' => StatusEnum::APPROVED])
            )
            ->state(['status' => StatusEnum::APPROVED])
            ->create();

        $response = $this->getJson('/api/v1/posts/');
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => ['title', 'content', 'status', 'date', 'creator', 'comments']
            ],
            'meta',
            'links'
        ]);
        $response->assertJsonPath('message', 'Posts retrieved successfully.');
    }

    public function test_store_creates_post_and_flags(): void
    {
        Http::fake([
            'localhost:8080/analyze' => Http::response([
                'is_flagged' => true,
                'reason' => 'Toxic content detected',
                'score' => 0.92,
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $payload = [
            'title' => 'bad post',
            'content' => 'toxic content',
            'ai_model' => 'toxic-bert',
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response->assertStatus(201);
        $response->assertJsonPath('data.status', StatusEnum::FLAGGED->value);
        $response->assertJsonPath('message', 'Post created successfully.');

        $this->assertDatabaseHas('posts', [
            'title' => $payload['title'],
            'status' => StatusEnum::FLAGGED->value,
            'user_id' => $user->id,
        ]);

        $post = Post::first();
        $this->assertDatabaseHas('filter_logs', [
            'content_id' => $post->id,
            'reason' => 'Toxic content detected',
            'confidence' => 0.92,
        ]);
    }

    public function test_show_returns_post_if_exists(): void
    {
        $response = $this->getJson('/api/v1/posts/5');
        $response->assertNotFound();
        $response->assertJsonPath('message', 'Post not found.');

        $user = User::factory()->create();
        Post::factory()
            ->count(1)
            ->for($user)
            ->has(
                Comment::factory()->count(1)->state(['status' => StatusEnum::APPROVED])
            )
            ->state(['status' => StatusEnum::APPROVED])
            ->create();

        $response = $this->getJson('/api/v1/posts/1');
        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'title', 'content', 'status', 'date', 'creator', 'comments'
            ],
        ]);
        $response->assertJsonPath('message', 'Post retrieved successfully.');
    }

    public function test_update_edits_post_with_valid_data(): void
    {
        Http::fake([
            'localhost:8080/analyze' => Http::response([
                'is_flagged' => false,
                'reason' => null,
                'score' => null,
            ], 200),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'status' => StatusEnum::FLAGGED->value, // start with flagged to test the change
        ]);

        $payload = [
            'title' => 'updated title',
            'content' => 'clean safe content',
            'ai_model' => 'toxic-bert',
        ];

        $response = $this->putJson("/api/v1/posts/{$post->id}", $payload);

        $response->assertStatus(200);
        $response->assertJsonPath('data.title', $payload['title']);
        $response->assertJsonPath('data.status', StatusEnum::APPROVED->value);
        $response->assertJsonPath('message', 'Post updated successfully.');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => $payload['title'],
            'status' => StatusEnum::APPROVED->value,
        ]);

        $this->assertDatabaseMissing('filter_logs', [
            'content_id' => $post->id,
        ]);
    }

    public function test_destroy_soft_deletes_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create([
            'user_id' => $user->id,
            'status' => StatusEnum::PENDING->value,
        ]);

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'message' => 'Post deleted successfully.',
            'data' => [],
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => StatusEnum::DELETED->value,
        ]);

        $this->assertSoftDeleted('posts', [
            'id' => $post->id,
        ]);

        $this->assertDatabaseMissing('filter_logs', [
            'content_id' => $post->id,
        ]);
    }

    public function test_restore_recovers_deleted_post(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = Post::factory()->create(attributes: [
            'user_id' => $user->id,
            'status' => StatusEnum::DELETED->value,
        ]);
        $post->update(['status' => StatusEnum::DELETED]);
        $post->delete();

        $response = $this->patchJson("/api/v1/posts/{$post->id}/restore");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 200,
            'message' => 'Post restored successfully.',
            'data' => [],
        ]);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => StatusEnum::PENDING->value,
        ]);

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'deleted_at' => $post->deleted_at,
        ]);
    }
}
