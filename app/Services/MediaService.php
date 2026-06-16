<?php

namespace App\Services;

use App\Models\MediaLibrary;
use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    /**
     * All library media, newest first.
     *
     * @return Collection<int, Media>
     */
    public function all(): Collection
    {
        return Media::query()
            ->where('collection_name', MediaLibrary::COLLECTION)
            ->latest()
            ->get();
    }

    /**
     * Store an uploaded image in the library with a slugified file name (FR-40).
     */
    public function upload(UploadedFile $file): Media
    {
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name) ?: 'image';
        $fileName = $slug.'-'.Str::lower(Str::random(6)).'.'.$file->getClientOriginalExtension();

        return MediaLibrary::instance()
            ->addMedia($file)
            ->usingName($name)
            ->usingFileName($fileName)
            ->withCustomProperties(['alt' => ''])
            ->toMediaCollection(MediaLibrary::COLLECTION);
    }

    /**
     * Update the alt text stored in the media custom properties (FR-41, FR-42).
     *
     * The library is the single source of truth for alt text, so a change here
     * propagates to every place that copied or referenced this image — featured
     * images and post content blocks — unless that place carries a deliberate
     * per-post override (an alt that no longer tracks the previous library value).
     */
    public function updateAltText(Media $media, ?string $alt): Media
    {
        $previous = (string) $media->getCustomProperty('alt', '');
        $new = $alt ?? '';

        $media->setCustomProperty('alt', $new);
        $media->save();

        if ($new !== $previous) {
            $this->propagateAlt($media, $previous, $new);
        }

        return $media;
    }

    /**
     * Push an alt-text change out to derived featured copies and content blocks.
     */
    protected function propagateAlt(Media $library, string $previous, string $new): void
    {
        Media::query()
            ->where('collection_name', Post::FEATURED_COLLECTION)
            ->where('custom_properties->library_media_id', $library->id)
            ->get()
            ->each(function (Media $copy) use ($previous, $new): void {
                if ((string) $copy->getCustomProperty('alt', '') === $previous) {
                    $copy->setCustomProperty('alt', $new);
                    $copy->save();
                }
            });

        $this->syncContentAlt($library, $previous, $new);
    }

    /**
     * Rewrite the alt of image blocks that reference this image. The block's
     * intrinsic alt (`file.alt`) always follows the library; the caption — which
     * is the rendered alt and may hold a per-post edit — is only updated when it
     * still tracks the previous library value.
     */
    protected function syncContentAlt(Media $library, string $previous, string $new): void
    {
        $url = $library->getUrl();

        // Match on file name too: the array cast escapes slashes in the stored
        // JSON, so a raw-URL LIKE would miss — the file name is slash-free.
        Post::query()
            ->where('content', 'like', '%'.$library->file_name.'%')
            ->orWhere('content', 'like', '%'.$url.'%')
            ->get(['id', 'content'])
            ->each(function (Post $post) use ($url, $previous, $new): void {
                $content = $post->content ?? [];
                $changed = false;

                foreach ($content['blocks'] ?? [] as $i => $block) {
                    if (! in_array($block['type'] ?? '', EditorJsRenderer::IMAGE_BLOCKS, true)) {
                        continue;
                    }

                    if (($block['data']['file']['url'] ?? null) !== $url) {
                        continue;
                    }

                    $content['blocks'][$i]['data']['file']['alt'] = $new;

                    if ((string) ($block['data']['caption'] ?? '') === $previous) {
                        $content['blocks'][$i]['data']['caption'] = $new;
                    }

                    $changed = true;
                }

                if ($changed) {
                    $post->content = $content;
                    $post->saveQuietly();
                }
            });
    }

    public function delete(Media $media): void
    {
        $media->delete();
    }

    /**
     * Posts that reference this media in their content (FR-44).
     *
     * @return Collection<int, Post>
     */
    public function usages(Media $media): Collection
    {
        return Post::query()
            ->where('content', 'like', '%'.$media->file_name.'%')
            ->orWhere('content', 'like', '%'.$media->getUrl().'%')
            ->get(['id', 'title', 'slug']);
    }
}
