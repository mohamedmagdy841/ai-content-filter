<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'title'   => $this->title,
            'content' => $this->content,
            'status'  => $this->status,
            'date'    => $this->created_at->format('Y-m-d h:m a'),
            'creator' => new UserResource($this->whenLoaded('user')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'images'   => ImageResource::collection($this->whenLoaded('images')),
            'tags'     => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
