<?php

namespace App\Jobs;

use App\Seo\SitemapService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RegenerateSitemap implements ShouldQueue
{
    use Queueable;

    public function handle(SitemapService $sitemap): void
    {
        $sitemap->store();
    }
}
