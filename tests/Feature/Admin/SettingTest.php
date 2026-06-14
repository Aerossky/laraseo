<?php

use App\Models\Setting;
use App\Models\User;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function settingsData(array $overrides = []): array
{
    return array_merge([
        'site_name' => 'My Blog',
        'meta_title_format' => '{title} — {site}',
        'meta_description_fallback' => 'A great blog.',
        'google_site_verification' => 'abc123',
        'head_scripts' => '<script>head()</script>',
        'body_scripts' => '<script>body()</script>',
        'robots_txt' => "User-agent: *\nDisallow:",
    ], $overrides);
}

it('requires authentication for the settings page', function () {
    auth()->logout();

    $this->get(route('admin.settings.index'))->assertRedirect(route('login'));
});

it('renders the settings page with defaults', function () {
    $this->get(route('admin.settings.index'))
        ->assertOk()
        ->assertSee('Meta title format')
        ->assertSee('robots.txt');
});

it('shows a stored value over the default', function () {
    Setting::set('site_name', 'Stored Name');

    $this->get(route('admin.settings.index'))->assertOk()->assertSee('Stored Name');
});

it('saves all known settings', function () {
    $this->put(route('admin.settings.update'), settingsData())
        ->assertRedirect(route('admin.settings.index'));

    expect(Setting::get('site_name'))->toBe('My Blog')
        ->and(Setting::get('meta_title_format'))->toBe('{title} — {site}')
        ->and(Setting::get('google_site_verification'))->toBe('abc123')
        ->and(Setting::get('robots_txt'))->toBe("User-agent: *\nDisallow:");
});

it('updates an existing setting in place', function () {
    Setting::set('site_name', 'Before');

    $this->put(route('admin.settings.update'), settingsData(['site_name' => 'After']));

    expect(Setting::get('site_name'))->toBe('After')
        ->and(Setting::where('key', 'site_name')->count())->toBe(1);
});

it('requires a site name', function () {
    $this->put(route('admin.settings.update'), settingsData(['site_name' => '']))
        ->assertSessionHasErrors('site_name');
});

it('ignores unknown keys in the payload', function () {
    $this->put(route('admin.settings.update'), settingsData(['malicious' => 'value']));

    expect(Setting::where('key', 'malicious')->exists())->toBeFalse();
});
