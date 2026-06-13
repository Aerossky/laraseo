<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * Singleton owner for general-purpose library uploads (EditorJS images, etc.).
 */
class MediaLibrary extends Model implements HasMedia
{
    use InteractsWithMedia;

    public const COLLECTION = 'library';

    protected $guarded = [];

    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::COLLECTION)
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }
}
