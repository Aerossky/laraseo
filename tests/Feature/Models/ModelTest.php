<?php

use App\Enums\PostStatus;
use App\Enums\RedirectType;
use App\Models\Category;
use App\Models\Post;
use App\Models\Redirect;
use App\Models\Setting;

it('generates a slug for a category from its name', function () {
    $category = Category::factory()->create(['name' => 'Search Engine Optimization']);

    expect($category->slug)->toBe('search-engine-optimization');
});

it('generates a slug for a post from its title', function () {
    $post = Post::factory()->create(['title' => 'My First Post']);

    expect($post->slug)->toBe('my-first-post');
});

it('casts post content to array and status to a PostStatus enum', function () {
    $post = Post::factory()->create();

    expect($post->content)->toBeArray()
        ->and($post->status)->toBeInstanceOf(PostStatus::class)
        ->and($post->show_toc)->toBeBool();
});

it('relates a post to its category', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->for($category)->create();

    expect($post->category->is($category))->toBeTrue()
        ->and($category->posts)->toHaveCount(1);
});

it('returns only currently published posts from the published scope', function () {
    Post::factory()->create();                 // draft
    Post::factory()->scheduled()->create();    // published_at in the future
    Post::factory()->published()->create();    // live

    expect(Post::published()->count())->toBe(1);
});

it('reports its published state correctly', function () {
    expect(Post::factory()->published()->create()->isPublished())->toBeTrue()
        ->and(Post::factory()->scheduled()->create()->isPublished())->toBeFalse()
        ->and(Post::factory()->create()->isPublished())->toBeFalse();
});

it('nulls the category_id when the category is deleted', function () {
    $post = Post::factory()->create();

    $post->category->delete();

    expect($post->fresh()->category_id)->toBeNull();
});

it('casts the redirect type to a RedirectType enum', function () {
    $redirect = Redirect::factory()->create(['type' => 302]);

    expect($redirect->type)->toBe(RedirectType::Temporary)
        ->and($redirect->is_active)->toBeBool();
});

it('stores and retrieves settings by key', function () {
    Setting::set('site_name', 'laraseo');

    expect(Setting::get('site_name'))->toBe('laraseo')
        ->and(Setting::get('missing_key', 'fallback'))->toBe('fallback')
        ->and(Setting::get('missing_key'))->toBeNull();
});
