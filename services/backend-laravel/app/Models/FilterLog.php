<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Log;

class FilterLog extends Model
{
    /** @use HasFactory<\Database\Factories\FilterLogFactory> */
    use HasFactory, Prunable;

    protected $fillable = [
        'content_type',
        'content_id',
        'flagged_by',
        'reason',
        'confidence',
        'status',
    ];

    public function content()
    {
        return $this->morphTo();
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }

    protected function pruning(): void
    {
        Log::info('Logs pruning');
    }

    public static function getFilteredPosts()
    {
        return self::where('content_type', Post::class)
            ->with('content')
            ->get()
            ->pluck('content');
    }
}
