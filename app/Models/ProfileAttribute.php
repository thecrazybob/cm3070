<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class ProfileAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'key_name',
        'display_name',
        'description',
        'data_type',
        'is_system',
        'validation_rules',
    ];

    protected $casts = [
        'is_system' => 'boolean',
        'validation_rules' => 'array',
    ];
}
