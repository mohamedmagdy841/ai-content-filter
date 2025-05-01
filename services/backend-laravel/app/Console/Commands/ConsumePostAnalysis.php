<?php

namespace App\Console\Commands;

use App\Enums\StatusEnum;
use App\Facades\RabbitMQ;
use App\Models\Post;
use App\Models\User;
use App\Notifications\ContentFlaggedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class ConsumePostAnalysis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:consume-post-analysis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consume messages from post.analysis.response queue';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $callback = function ($msg) {
            $data = json_decode($msg->body, true);

            Log::info('Message consumed from RabbitMQ', $data);

            $post = Post::find($data['post_id']);
            if (!$post) return;

            $post->update(['status' => $data['status']]);

            if ($data['status'] === \App\Enums\StatusEnum::FLAGGED->value) {
                $post->filterLogs()->create([
                    'reason' => $data["reason"],
                    'confidence' => $data["score"] ?? null,
                ]);

                $admins = \App\Models\User::role('admin')->get();
                Notification::send($admins, new ContentFlaggedNotification($post, 'post'));
            }
        };

        RabbitMQ::consume('post.analysis.response', $callback);
        Log::info('Received message from RabbitMQ', [$callback]);
    }
}
