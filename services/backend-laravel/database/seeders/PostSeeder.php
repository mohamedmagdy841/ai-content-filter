<?php

namespace Database\Seeders;

use App\Enums\StatusEnum;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = \App\Models\Tag::factory()->count(5)->create();

        Post::factory()->count(5)->create()->each(function ($post) use ($tags) {

            $post->images()->create([
                'path' => 'https://picsum.photos/seed/' . uniqid() . '/600/400',
            ]);

            $post->tags()->attach($tags->random(rand(1, 3))->pluck('id'));

            $comments = Comment::factory()->count(3)->create(['post_id' => $post->id]);

            if ($post->status === StatusEnum::FLAGGED) {
                $post->filterLogs()->create([
                    'reason' => fake()->sentence(),
                    'confidence' => fake()->randomFloat(2, 0.5, 1),
                ]);
            }

            $comments->each(function ($comment) {
                $comment->images()->create([
                    'path' => 'https://picsum.photos/seed/' . uniqid() . '/400/300',
                ]);
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
