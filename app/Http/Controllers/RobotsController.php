<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        $robots = Setting::get('robots_txt', "User-agent: *\nDisallow: /admin\n");

        // Always advertise the sitemap (FR-30, FR-74).
        $robots = rtrim((string) $robots)."\n\nSitemap: ".url('/sitemap.xml')."\n";

        return response($robots, 200, ['Content-Type' => 'text/plain']);
    }
}
