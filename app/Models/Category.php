<?php

namespace App\Models;

use App\Seo\HasSeoMeta;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory, HasSeoMeta, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function getSeoTitle(): ?string
    {
        return $this->name;
    }

    public function getSeoDescription(): ?string
    {
        return $this->description;
    }
}
