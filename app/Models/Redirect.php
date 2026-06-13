<?php

namespace App\Models;

use App\Enums\RedirectType;
use Database\Factories\RedirectFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    /** @use HasFactory<RedirectFactory> */
    use HasFactory;

    protected $fillable = [
        'from_url',
        'to_url',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => RedirectType::class,
            'is_active' => 'boolean',
        ];
    }
}
