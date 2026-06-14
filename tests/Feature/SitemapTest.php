<?php

use App\Enums\PostStatus;
use App\Jobs\RegenerateSitemap;
use App\Models\Category;
use App\Models\Post;
use App\Seo\SitemapService;
use Illuminate\Support\Facades\Queue;

it('serves the sitemap as XML at /sitemap.xml', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml');
});

it('includes published posts and category pages but not drafts', function () {
    $published = Post::factory()->published()->create();
    $draft = Post::factory()->create();
    $category = Category::factory()->create();

    // The publish observer regenerates the cache mid-setup; refresh it so the
    // request reflects everything created above.
    app(SitemapService::class)->forget();

    $body = $this->get('/sitemap.xml')->getContent();

    expect($body)->toContain('/blog/'.$published->slug)
        ->and($body)->toContain('/blog/category/'.$category->slug)
        ->and($body)->not->toContain('/blog/'.$draft->slug);
});

it('queues a sitemap regeneration when a post is published', function () {
    Queue::fake();

    Post::factory()->published()->create();

    Queue::assertPushed(RegenerateSitemap::class);
});

it('does not regenerate the sitemap for a draft post', function () {
    Queue::fake();

    Post::factory()->create(); // draft

    Queue::assertNotPushed(RegenerateSitemap::class);
});

it('regenerates when a published post is unpublished', function () {
    $post = Post::factory()->published()->create();

    Queue::fake();
    $post->update(['status' => PostStatus::Draft]);

    Queue::assertPushed(RegenerateSitemap::class);
});

it('builds fresh sitemap content from the service', function () {
    Post::factory()->published()->create(['slug' => 'hello-world']);

    $xml = app(SitemapService::class)->build();

    expect($xml)->toContain('<urlset')
        ->and($xml)->toContain('/blog/hello-world');
});
