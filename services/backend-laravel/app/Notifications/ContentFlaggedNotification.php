<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContentFlaggedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $content;
    public $type;

    public function __construct($content, $type)
    {
        $this->content = $content;
        $this->type = $type;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => $this->type,
            'id' => $this->content->id,
            'user' => $this->content->user->name,
            'content' => $this->content->content,
            'created_at' => now()->toDateTimeString(),
        ];
    }
}
