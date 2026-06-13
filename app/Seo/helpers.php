<?php

use App\Seo\SeoService;

if (! function_exists('seo')) {
    /**
     * Resolve the shared SeoService instance.
     */
    function seo(): SeoService
    {
        return app(SeoService::class);
    }
}
