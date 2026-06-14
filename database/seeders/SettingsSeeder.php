<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Persists the baseline global SEO settings. These mirror SettingService's
 * defaults so a fresh install has concrete, editable values in the admin panel
 * rather than relying only on runtime fallbacks. Idempotent.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => (string) config('app.name', 'laraseo'),
            'meta_title_format' => '{title} · {site}',
            'meta_description_fallback' => 'An SEO-first Laravel blog starter kit.',
            'robots_txt' => "User-agent: *\nDisallow: /admin\n",
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
