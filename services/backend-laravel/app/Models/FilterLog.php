<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterLog extends Model
{
    /** @use HasFactory<\Database\Factories\FilterLogFactory> */
    use HasFactory;

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
}
