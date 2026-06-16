<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Observers\PostObserver;
use App\Seo\HasSeoMeta;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

#[ObservedBy([PostObserver::class])]
class Post extends Model implements HasMedia
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, HasSeoMeta, HasSlug, InteractsWithMedia, SoftDeletes;

    /** The media collection holding the post's featured image. */
    public const FEATURED_COLLECTION = 'featured';

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'status',
        'show_toc',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'status' => PostStatus::class,
            'show_toc' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->preventOverwrite()          // allow a manually edited slug (FR-08)
            ->doNotGenerateSlugsOnUpdate(); // keep permalinks stable when the title changes
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::FEATURED_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<User, $this> */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /** @return HasMany<Comment, $this> */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** @return HasMany<Comment, $this> */
    public function approvedComments(): HasMany
    {
        return $this->comments()->approved()->latest();
    }

    public function getSeoTitle(): ?string
    {
        return $this->title;
    }

    public function getSeoDescription(): ?string
    {
        return $this->getExcerpt() ?: null;
    }

    /**
     * The manual excerpt, or an auto-excerpt derived from the first paragraph.
     */
    public function getExcerpt(int $length = 160): string
    {
        if ($this->excerpt) {
            return $this->excerpt;
        }

        $text = collect($this->content['blocks'] ?? [])
            ->firstWhere('type', 'paragraph')['data']['text'] ?? '';

        return Str::limit(trim(strip_tags($text)), $length);
    }

    public function getSeoImageUrl(): ?string
    {
        $url = $this->getFirstMediaUrl(self::FEATURED_COLLECTION);

        return $url !== '' ? $url : null;
    }

    public function isPublished(): bool
    {
        return $this->status === PostStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /** @param Builder<Post> $query */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', PostStatus::Published)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
