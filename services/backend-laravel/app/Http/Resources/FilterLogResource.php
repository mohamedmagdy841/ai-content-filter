<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FilterLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $content = $this->content;
        return [
            'flagged_by' => $this->flagged_by,
            'reason' => $this->reason,
            'confidence' => $this->confidence,
            'created_at' => $this->created_at->format('Y-m-d h:m a'),
            'updated_at' => $this->updated_at->format('Y-m-d h:m a'),
            'content' => $content instanceof \App\Models\Post
                        ? new PostResource($content)
                        : ($content instanceof \App\Models\Comment
                            ? new CommentResource($content)
                            : null),
        ];
    }
}
