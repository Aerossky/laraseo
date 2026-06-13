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
     */
    public function updateAltText(Media $media, ?string $alt): Media
    {
        $media->setCustomProperty('alt', $alt ?? '');
        $media->save();

        return $media;
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
