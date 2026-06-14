<?php

namespace App\Services;

use App\Models\Setting;

class SettingService
{
    /**
     * The known settings keys and their fallback values. Anything not stored
     * in the database falls back to these defaults.
     *
     * @return array<string, string>
     */
    public function defaults(): array
    {
        return [
            'site_name' => (string) config('app.name', 'laraseo'),
            'meta_title_format' => '{title} · {site}',
            'meta_description_fallback' => '',
            'google_site_verification' => '',
            'head_scripts' => '',
            'body_scripts' => '',
            'robots_txt' => "User-agent: *\nDisallow: /admin\n",
        ];
    }

    /**
     * Stored settings merged over the defaults.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $stored = Setting::query()->pluck('value', 'key')->all();

        return collect($this->defaults())
            ->map(fn (string $default, string $key) => $stored[$key] ?? $default)
            ->all();
    }

    /**
     * Persist only the known settings keys present in the payload.
     *
     * @param  array<string, mixed>  $data
     */
    public function save(array $data): void
    {
        foreach (array_keys($this->defaults()) as $key) {
            if (array_key_exists($key, $data)) {
                Setting::set($key, $data[$key] !== null ? (string) $data[$key] : null);
            }
        }
    }
}
