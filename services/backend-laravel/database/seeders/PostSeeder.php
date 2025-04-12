<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Post::factory()->count(5)->create()->each(function ($post) {

            $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

            if ($post->status === StatusEnum::FLAGGED) {
                $post->filterLogs()->create([
                    'reason' => fake()->sentence(),
                    'confidence' => fake()->randomFloat(2, 0.5, 1),
                ]);
            }

            $comments->each(function ($comment) {
                if ($comment->status === StatusEnum::FLAGGED) {
                    $comment->filterLogs()->create([
                        'reason' => fake()->sentence(),
                        'confidence' => fake()->randomFloat(2, 0, 1),
                    ]);
                }
            });
        });
    }
}
