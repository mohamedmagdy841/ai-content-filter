<?php

namespace App\Models;

use App\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
    use HasFactory, SoftDeletes, Prunable;

    protected $fillable = [
        'user_id',
        'content',
        'status',
        'title',
    ];

    protected $casts = [
        'status' => StatusEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function filterLogs(): MorphMany
    {
        return $this->morphMany(FilterLog::class, 'content');
    }

    #[Scope]
    public function approved(Builder $query): void
    {
        $query->where('status', 'approved');
    }

    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subWeek());
    }

    protected function pruning(): void
    {
        Log::info('Posts pruning');
    }
}
