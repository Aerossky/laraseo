<?php

namespace App\Seo;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

/**
 * Builds and caches the XML sitemap from published content (FR-30–FR-34).
 *
 * The rendered XML is cached; regeneration is triggered off-request by the
 * RegenerateSitemap job so publishing never blocks on sitemap building (NFR-01).
 */
class SitemapService
{
    public const CACHE_KEY = 'sitemap.xml';

    /**
     * Build the sitemap XML from published posts and category pages.
     */
    public function build(): string
    {
        $sitemap = Sitemap::create()
            ->add(
                Url::create(url('/blog'))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.9)
            );

        Post::query()->published()->get()->each(function (Post $post) use ($sitemap) {
            $sitemap->add(
                Url::create(url('/blog/'.$post->slug))
                    ->setLastModificationDate($post->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        Category::query()->get()->each(function (Category $category) use ($sitemap) {
            $sitemap->add(
                Url::create(url('/blog/category/'.$category->slug))
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6)
            );
        });

        return $sitemap->render();
    }

    /**
     * Rebuild the sitemap and cache the result.
     */
    public function store(): string
    {
        $xml = $this->build();

        Cache::forever(self::CACHE_KEY, $xml);

        return $xml;
    }

    /**
     * Return the cached sitemap, building it on demand if missing.
     */
    public function get(): string
    {
        return Cache::get(self::CACHE_KEY) ?? $this->store();
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
