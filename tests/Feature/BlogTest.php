<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;

it('lists published posts but not drafts on the blog index', function () {
    $live = Post::factory()->published()->create(['title' => 'Live Article']);
    $draft = Post::factory()->create(['title' => 'Hidden Draft']);

    $this->get(route('blog.index'))
        ->assertOk()
        ->assertSee('Live Article')
        ->assertDontSee('Hidden Draft');
});

it('renders a single published post with the title as the only H1', function () {
    $post = Post::factory()->published()->create([
        'title' => 'My Great Post',
        'content' => ['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Hello body']]]],
    ]);

    $html = $this->get(route('blog.show', $post))->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1)
        ->and($html)->toContain('My Great Post')
        ->and($html)->toContain('Hello body');
});

it('returns 404 for a draft post', function () {
    $draft = Post::factory()->create();

    $this->get(route('blog.show', $draft))->assertNotFound();
});

it('renders SEO head tags and article JSON-LD on a post', function () {
    $post = Post::factory()->published()->create();

    $html = $this->get(route('blog.show', $post))->getContent();

    expect($html)->toContain('property="og:title"')
        ->and($html)->toContain('rel="canonical"')
        ->and($html)->toContain('application/ld+json')
        ->and($html)->toContain('"@type":"Article"')
        ->and($html)->toContain('"@type":"BreadcrumbList"');
});

it('applies the {title}/{site} meta title format from settings', function () {
    Setting::set('site_name', 'laraseo');
    Setting::set('meta_title_format', '{title} · {site}');
    $post = Post::factory()->published()->create(['title' => 'Hello']);

    $this->get(route('blog.show', $post))
        ->assertSee('<title>Hello · laraseo</title>', false);
});

it('shows a category page with its name as H1 and its posts', function () {
    $category = Category::factory()->create(['name' => 'Tutorials']);
    $post = Post::factory()->published()->for($category)->create(['title' => 'In Category']);
    Post::factory()->published()->create(['title' => 'Other Post']);

    $this->get(route('blog.category', $category))
        ->assertOk()
        ->assertSee('Tutorials')
        ->assertSee('In Category')
        ->assertDontSee('Other Post');
});

it('serves robots.txt with the sitemap reference', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap:');
});
