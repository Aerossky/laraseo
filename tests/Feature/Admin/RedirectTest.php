<?php

use App\Enums\RedirectType;
use App\Models\Redirect;
use App\Models\User;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function redirectData(array $overrides = []): array
{
    return array_merge([
        'from_url' => '/old-page',
        'to_url' => '/new-page',
        'type' => RedirectType::Permanent->value,
        'is_active' => '1',
    ], $overrides);
}

it('requires authentication for the redirects area', function () {
    auth()->logout();

    $this->get(route('admin.redirects.index'))->assertRedirect(route('login'));
});

it('lists redirects', function () {
    Redirect::factory()->create(['from_url' => '/listed-here']);

    $this->get(route('admin.redirects.index'))->assertOk()->assertSee('/listed-here');
});

it('renders the create form', function () {
    $this->get(route('admin.redirects.create'))->assertOk();
});

it('creates a redirect', function () {
    $this->post(route('admin.redirects.store'), redirectData())
        ->assertRedirect(route('admin.redirects.index'));

    $redirect = Redirect::first();

    expect($redirect->from_url)->toBe('/old-page')
        ->and($redirect->to_url)->toBe('/new-page')
        ->and($redirect->type)->toBe(RedirectType::Permanent)
        ->and($redirect->is_active)->toBeTrue();
});

it('normalizes a from path without a leading slash', function () {
    $this->post(route('admin.redirects.store'), redirectData(['from_url' => 'no-slash']));

    expect(Redirect::first()->from_url)->toBe('/no-slash');
});

it('stores a temporary redirect type', function () {
    $this->post(route('admin.redirects.store'), redirectData(['type' => RedirectType::Temporary->value]));

    expect(Redirect::first()->type)->toBe(RedirectType::Temporary);
});

it('requires from and to urls', function () {
    $this->post(route('admin.redirects.store'), redirectData(['from_url' => '', 'to_url' => '']))
        ->assertSessionHasErrors(['from_url', 'to_url']);

    expect(Redirect::count())->toBe(0);
});

it('rejects a duplicate from url', function () {
    Redirect::factory()->create(['from_url' => '/taken']);

    $this->post(route('admin.redirects.store'), redirectData(['from_url' => '/taken']))
        ->assertSessionHasErrors('from_url');
});

it('rejects a redirect that points to itself', function () {
    $this->post(route('admin.redirects.store'), redirectData(['from_url' => '/loop', 'to_url' => '/loop']))
        ->assertSessionHasErrors('to_url');
});

it('refuses to redirect the admin panel', function () {
    $this->post(route('admin.redirects.store'), redirectData(['from_url' => '/admin/posts']))
        ->assertSessionHasErrors('from_url');
});

it('updates a redirect', function () {
    $redirect = Redirect::factory()->create(['to_url' => '/before']);

    $this->put(route('admin.redirects.update', $redirect), redirectData([
        'from_url' => $redirect->from_url,
        'to_url' => '/after',
    ]))->assertRedirect(route('admin.redirects.index'));

    expect($redirect->fresh()->to_url)->toBe('/after');
});

it('toggles the active state', function () {
    $redirect = Redirect::factory()->create(['is_active' => true]);

    $this->patch(route('admin.redirects.toggle', $redirect))->assertRedirect();
    expect($redirect->fresh()->is_active)->toBeFalse();

    $this->patch(route('admin.redirects.toggle', $redirect));
    expect($redirect->fresh()->is_active)->toBeTrue();
});

it('deletes a redirect', function () {
    $redirect = Redirect::factory()->create();

    $this->delete(route('admin.redirects.destroy', $redirect))->assertRedirect();

    expect(Redirect::count())->toBe(0);
});
