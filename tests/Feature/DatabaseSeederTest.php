<?php

use App\Enums\PostStatus;
use App\Enums\RedirectType;
use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;

it('seeds the default admin account', function () {
    $this->seed();

    $admin = User::where('email', 'admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and(Hash::check('password', $admin->password))->toBeTrue();
});

it('seeds a published sample post with seo meta in a category', function () {
    $this->seed();

    $post = Post::where('slug', 'welcome-to-laraseo')->first();

    expect($post)->not->toBeNull()
        ->and($post->status)->toBe(PostStatus::Published)
        ->and($post->isPublished())->toBeTrue()
        ->and($post->category->slug)->toBe('getting-started')
        ->and($post->seoMeta->meta_title)->toContain('laraseo');
});

it('seeds content covering every renderer block type', function () {
    $this->seed();

    $types = collect(Post::where('slug', 'welcome-to-laraseo')->first()->content['blocks'])
        ->pluck('type')->unique();

    expect($types)->toContain('paragraph', 'header', 'list', 'quote', 'image', 'code', 'table', 'delimiter');
});

it('seeds an active 301 redirect', function () {
    $this->seed();

    $redirect = Redirect::where('from_url', '/welcome')->first();

    expect($redirect)->not->toBeNull()
        ->and($redirect->type)->toBe(RedirectType::Permanent)
        ->and($redirect->is_active)->toBeTrue();
});

it('seeds baseline global settings', function () {
    $this->seed();

    expect(Setting::get('site_name'))->not->toBeNull()
        ->and(Setting::get('meta_title_format'))->toBe('{title} · {site}');
});

it('is idempotent when content seeders run twice', function () {
    $this->seed(DemoContentSeeder::class);
    $this->seed(DemoContentSeeder::class);

    expect(Post::where('slug', 'welcome-to-laraseo')->count())->toBe(1)
        ->and(Category::where('slug', 'getting-started')->count())->toBe(1)
        ->and(Redirect::where('from_url', '/welcome')->count())->toBe(1);
});
