<?php

namespace App\Observers;

use App\Enums\PostStatus;
use App\Jobs\RegenerateSitemap;
use App\Models\Post;

class PostObserver
{
    public function saved(Post $post): void
    {
        // Regenerate only when the published state is involved (FR-31, NFR-01).
        if ($this->isPublished($post->status) || $this->isPublished($post->getOriginal('status'))) {
            RegenerateSitemap::dispatch();
        }
    }

    public function deleted(Post $post): void
    {
        if ($this->isPublished($post->status)) {
            RegenerateSitemap::dispatch();
        }
    }

    private function isPublished(PostStatus|string|null $status): bool
    {
        $value = $status instanceof PostStatus ? $status->value : $status;

        return $value === PostStatus::Published->value;
    }
}
