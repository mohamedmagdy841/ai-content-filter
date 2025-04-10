<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'content' => $this->content,
            'status'  => $this->status,
            'date'    => $this->created_at->format('y-m-d h:m a'),
            'post' => new PostResource($this->whenLoaded('post')),
            'creator' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
