<?php

use App\Models\Post;
use App\Models\Setting;
use App\Seo\SeoService;

function seoService(): SeoService
{
    return app(SeoService::class);
}

it('falls back to the post title and excerpt', function () {
    $post = Post::factory()->create(['title' => 'Hello World', 'excerpt' => 'An intro']);

    $data = seoService()->for($post)->resolve();

    expect($data['raw_title'])->toBe('Hello World')
        ->and($data['description'])->toBe('An intro')
        ->and($data['robots'])->toBe('index, follow')
        ->and($data['og_type'])->toBe('article');
});

it('prefers the seo meta record over model defaults', function () {
    $post = Post::factory()->create(['title' => 'Hello World']);
    $post->seo()->update(['meta_title' => 'Custom SEO Title', 'robots' => 'noindex, nofollow']);

    $data = seoService()->for($post->fresh())->resolve();

    expect($data['raw_title'])->toBe('Custom SEO Title')
        ->and($data['robots'])->toBe('noindex, nofollow');
});

it('lets an explicit override win over everything', function () {
    $post = Post::factory()->create(['title' => 'Hello World']);
    $post->seo()->update(['meta_title' => 'Meta Title']);

    $data = seoService()->for($post->fresh())->title('Override')->resolve();

    expect($data['raw_title'])->toBe('Override');
});

it('falls open graph fields back to meta fields', function () {
    $post = Post::factory()->create(['title' => 'T', 'excerpt' => 'D']);

    $data = seoService()->for($post)->resolve();

    expect($data['og_title'])->toBe('T')
        ->and($data['og_description'])->toBe('D');
});

it('auto-sets the canonical url to the current url when not provided', function () {
    $post = Post::factory()->create();

    $data = seoService()->for($post)->resolve();

    expect($data['canonical'])->toBe(url()->current());
});

it('applies the configured meta title format', function () {
    Setting::set('site_name', 'laraseo');
    Setting::set('meta_title_format', ':title | :site');
    $post = Post::factory()->create(['title' => 'Hello']);

    $data = seoService()->for($post)->resolve();

    expect($data['title'])->toBe('Hello | laraseo');
});

it('renders the core head tags including json-ld', function () {
    $post = Post::factory()->published()->create(['title' => 'Hello', 'excerpt' => 'Desc']);

    $html = (string) seoService()->for($post)->render();

    expect($html)
        ->toContain('<title>')
        ->toContain('rel="canonical"')
        ->toContain('name="robots"')
        ->toContain('property="og:title"')
        ->toContain('name="twitter:card"')
        ->toContain('application/ld+json');
});

it('outputs the google site verification tag when set', function () {
    Setting::set('google_site_verification', 'abc123');

    $html = (string) seoService()->render();

    expect($html)->toContain('name="google-site-verification"')
        ->toContain('abc123');
});

it('creates a seo meta record on demand', function () {
    $post = Post::factory()->create();

    expect($post->seoMeta)->toBeNull();

    $post->seo();

    expect($post->fresh()->seoMeta)->not->toBeNull();
});
