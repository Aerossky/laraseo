<?php

namespace App\Services;

use App\Models\MediaLibrary;
use App\Models\Post;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class PostService
{
    public function __construct(protected MediaService $media) {}

    public function create(array $data, ?UploadedFile $featured = null): Post
    {
        return DB::transaction(function () use ($data, $featured) {
            $post = Post::create($this->attributes($data));
            $this->syncSeo($post, $data['seo'] ?? []);
            $this->syncFeatured($post, $featured, $data['featured_alt'] ?? null, $data['featured_media_id'] ?? null);

            return $post;
        });
    }

    public function update(Post $post, array $data, ?UploadedFile $featured = null): Post
    {
        return DB::transaction(function () use ($post, $data, $featured) {
            $post->update($this->attributes($data));
            $this->syncSeo($post, $data['seo'] ?? []);
            $this->syncFeatured($post, $featured, $data['featured_alt'] ?? null, $data['featured_media_id'] ?? null);

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

            if (in_array($block['type'] ?? '', EditorJsRenderer::IMAGE_BLOCKS, true)) {
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
            'title', 'slug', 'category_id', 'author_id', 'excerpt', 'content', 'status', 'published_at', 'show_toc',
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

    /**
     * Set the post's featured image. Every image is funneled through the media
     * library so it stays the single catalog: a freshly uploaded file is added
     * to the library first, a selection by id reuses an existing library image,
     * and in both cases the chosen image is copied into the post's `featured`
     * collection. With neither, only the existing image's alt text is refreshed.
     */
    protected function syncFeatured(Post $post, ?UploadedFile $file, ?string $alt, ?int $libraryMediaId = null): void
    {
        $source = match (true) {
            $file !== null => $this->media->upload($file),
            $libraryMediaId !== null => $this->libraryMedia($libraryMediaId),
            default => null,
        };

        if (! $source) {
            if ($alt !== null && ($current = $post->getFirstMedia(Post::FEATURED_COLLECTION))) {
                $current->setCustomProperty('alt', $alt);
                $current->save();
            }

            return;
        }

        // A brand-new upload has no other usages yet, so seed the library entry's
        // alt from the form. For an existing pick, leave the library entry alone —
        // the form value is treated as a post-level override and must not cascade
        // back to other posts; canonical alt edits happen on the media library page.
        if ($file !== null && $alt !== null && $alt !== '') {
            $source->setCustomProperty('alt', $alt);
            $source->save();
        }

        $post->clearMediaCollection(Post::FEATURED_COLLECTION);
        $copy = $source->copy($post, Post::FEATURED_COLLECTION);
        $copy->setCustomProperty('alt', $alt ?: $source->getCustomProperty('alt', ''));
        $copy->setCustomProperty('library_media_id', $source->id);
        $copy->save();
    }

    /**
     * Resolve a media record that belongs to the shared media library.
     */
    protected function libraryMedia(int $id): ?Media
    {
        return Media::query()
            ->where('collection_name', MediaLibrary::COLLECTION)
            ->find($id);
    }
}
