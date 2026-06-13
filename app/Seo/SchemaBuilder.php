<?php

namespace App\Seo;

use App\Models\Post;
use App\Models\Setting;
use Illuminate\Support\HtmlString;

/**
 * Builds Schema.org JSON-LD structured data (FR-23).
 */
class SchemaBuilder
{
    /**
     * Build Article JSON-LD for a blog post.
     *
     * @return array<string, mixed>
     */
    public function article(Post $post): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->getSeoTitle(),
            'description' => $post->getSeoDescription(),
            'image' => $post->getSeoImageUrl(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => url()->current(),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => Setting::get('site_name', config('app.name')),
            ],
        ];

        return array_filter($schema, fn ($value) => $value !== null);
    }

    /**
     * Build BreadcrumbList JSON-LD.
     *
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbs(array $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $item, int $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], $items, array_keys($items)),
        ];
    }

    /**
     * Render one or more schema arrays as JSON-LD <script> tags.
     *
     * @param  array<string, mixed>  ...$schemas
     */
    public function toScript(array ...$schemas): HtmlString
    {
        $scripts = array_map(function (array $schema) {
            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return '<script type="application/ld+json">'.$json.'</script>';
        }, $schemas);

        return new HtmlString(implode("\n", $scripts));
    }
}
