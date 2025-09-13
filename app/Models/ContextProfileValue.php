<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContextProfileValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'context_id',
        'attribute_id',
        'value',
        'visibility',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProfileAttribute::class, 'attribute_id');
    }
}
