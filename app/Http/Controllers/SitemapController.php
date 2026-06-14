<?php

namespace App\Http\Controllers;

use App\Seo\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(SitemapService $sitemap): Response
    {
        return response($sitemap->get(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
