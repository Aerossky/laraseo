<?php

namespace App\Seo;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Gives a model a polymorphic SEO meta record and per-model SEO defaults.
 *
 * Models may override getSeoTitle(), getSeoDescription() and getSeoImageUrl()
 * to provide fallbacks used by SeoService when no SeoMeta value is set.
 */
trait HasSeoMeta
{
    public static function bootHasSeoMeta(): void
    {
        static::deleting(function ($model) {
            // Keep SEO meta on soft delete; only purge on a hard/force delete.
            $isForceDeleting = method_exists($model, 'isForceDeleting')
                ? $model->isForceDeleting()
                : true;

            if ($isForceDeleting) {
                $model->seoMeta()->delete();
            }
        });
    }

    /** @return MorphOne<SeoMeta, $this> */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Get the SEO meta record, creating an empty one if it does not exist (FR-21).
     */
    public function seo(): SeoMeta
    {
        return $this->seoMeta()->firstOrCreate([]);
    }

    /** Default SEO title used as a fallback — override per model. */
    public function getSeoTitle(): ?string
    {
        return null;
    }

    /** Default SEO description used as a fallback — override per model. */
    public function getSeoDescription(): ?string
    {
        return null;
    }

    /** Default OG/social image URL used as a fallback — override per model. */
    public function getSeoImageUrl(): ?string
    {
        return null;
    }
}
