<?php

use App\Enums\RedirectType;
use App\Models\Redirect;

it('redirects an active from_url to its target with a 301', function () {
    Redirect::factory()->create([
        'from_url' => '/old-page',
        'to_url' => '/new-page',
        'type' => RedirectType::Permanent,
        'is_active' => true,
    ]);

    $this->get('/old-page')
        ->assertStatus(301)
        ->assertRedirect('/new-page');
});

it('uses a 302 for temporary redirects', function () {
    Redirect::factory()->create([
        'from_url' => '/temp',
        'to_url' => '/destination',
        'type' => RedirectType::Temporary,
        'is_active' => true,
    ]);

    $this->get('/temp')
        ->assertStatus(302)
        ->assertRedirect('/destination');
});

it('matches a from_url stored without a leading slash', function () {
    Redirect::factory()->create([
        'from_url' => 'legacy',
        'to_url' => '/modern',
        'is_active' => true,
    ]);

    $this->get('/legacy')->assertRedirect('/modern');
});

it('does not redirect an inactive redirect', function () {
    Redirect::factory()->inactive()->create([
        'from_url' => '/old-page',
        'to_url' => '/new-page',
    ]);

    $this->get('/old-page')->assertNotFound();
});

it('never redirects admin paths', function () {
    // A redirect that would hijack the admin panel must be ignored (FR-38).
    Redirect::factory()->create([
        'from_url' => '/admin/posts',
        'to_url' => '/hijacked',
        'is_active' => true,
    ]);

    // Unauthenticated /admin/posts should hit the auth guard, not the redirect.
    $this->get('/admin/posts')->assertRedirect(route('login'));
});

it('ignores non-safe (POST) requests', function () {
    Redirect::factory()->create([
        'from_url' => '/old-page',
        'to_url' => '/new-page',
        'is_active' => true,
    ]);

    $this->post('/old-page')->assertNotFound();
});
