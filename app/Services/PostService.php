<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PostService
{
    public function create(array $data, ?UploadedFile $featured = null): Post
    {
        return DB::transaction(function () use ($data, $featured) {
            $post = Post::create($this->attributes($data));
            $this->syncSeo($post, $data['seo'] ?? []);
            $this->syncFeatured($post, $featured, $data['featured_alt'] ?? null);

            return $post;
        });
    }

    public function update(Post $post, array $data, ?UploadedFile $featured = null): Post
    {
        return DB::transaction(function () use ($post, $data, $featured) {
            $post->update($this->attributes($data));
            $this->syncSeo($post, $data['seo'] ?? []);
            $this->syncFeatured($post, $featured, $data['featured_alt'] ?? null);

            return $post;
        });
    }

    /**
     * Count images across the content that are missing alt text (FR-15).
     * Image blocks use the caption as alt; gallery items use the media alt.
     */
    public static function imageBlocksMissingAlt(?array $content): int
    {
        $missing = 0;

        foreach ($content['blocks'] ?? [] as $block) {
            $data = $block['data'] ?? [];

            if (($block['type'] ?? '') === 'image') {
                $alt = trim((string) ($data['caption'] ?? '')) ?: trim((string) ($data['file']['alt'] ?? ''));
                $missing += $alt === '' ? 1 : 0;
            }

            if (($block['type'] ?? '') === 'gallery') {
                foreach ($data['files'] ?? [] as $file) {
                    $missing += trim((string) ($file['alt'] ?? '')) === '' ? 1 : 0;
                }
            }
        }

        return $missing;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function attributes(array $data): array
    {
        return Arr::only($data, [
            'title', 'slug', 'category_id', 'excerpt', 'content', 'status', 'published_at', 'show_toc',
        ]);
    }

    /**
     * @param  array<string, mixed>  $seo
     */
    protected function syncSeo(Post $post, array $seo): void
    {
        $post->seoMeta()->updateOrCreate([], [
            'meta_title' => $seo['meta_title'] ?? null,
            'meta_description' => $seo['meta_description'] ?? null,
            'canonical_url' => $seo['canonical_url'] ?? null,
            'robots' => $seo['robots'] ?? 'index, follow',
            'og_title' => $seo['og_title'] ?? null,
            'og_description' => $seo['og_description'] ?? null,
            'og_image' => $seo['og_image'] ?? null,
        ]);
    }

    protected function syncFeatured(Post $post, ?UploadedFile $file, ?string $alt): void
    {
        if ($file) {
            $post->clearMediaCollection('featured');
            $post->addMedia($file)
                ->withCustomProperties(['alt' => $alt ?? ''])
                ->toMediaCollection('featured');

            return;
        }

        if ($alt !== null && ($media = $post->getFirstMedia('featured'))) {
            $media->setCustomProperty('alt', $alt);
            $media->save();
        }
    }
}
