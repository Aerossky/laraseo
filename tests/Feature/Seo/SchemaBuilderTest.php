<?php

use App\Models\Post;
use App\Seo\SchemaBuilder;

it('builds article schema from a post', function () {
    $post = Post::factory()->published()->create(['title' => 'My Post', 'excerpt' => 'Summary']);

    $schema = app(SchemaBuilder::class)->article($post);

    expect($schema['@context'])->toBe('https://schema.org')
        ->and($schema['@type'])->toBe('Article')
        ->and($schema['headline'])->toBe('My Post')
        ->and($schema['description'])->toBe('Summary')
        ->and($schema['datePublished'])->not->toBeNull()
        ->and($schema['publisher']['@type'])->toBe('Organization');
});

it('omits null fields from article schema', function () {
    $post = Post::factory()->create(['excerpt' => null]);

    $schema = app(SchemaBuilder::class)->article($post);

    expect($schema)->not->toHaveKey('description')
        ->and($schema)->not->toHaveKey('image')
        ->and($schema)->not->toHaveKey('datePublished');
});

it('builds breadcrumb list schema with ordered positions', function () {
    $schema = app(SchemaBuilder::class)->breadcrumbs([
        ['name' => 'Home', 'url' => 'https://example.test'],
        ['name' => 'Blog', 'url' => 'https://example.test/blog'],
    ]);

    expect($schema['@type'])->toBe('BreadcrumbList')
        ->and($schema['itemListElement'])->toHaveCount(2)
        ->and($schema['itemListElement'][0]['position'])->toBe(1)
        ->and($schema['itemListElement'][1]['position'])->toBe(2)
        ->and($schema['itemListElement'][1]['name'])->toBe('Blog');
});

it('renders schema as a json-ld script tag', function () {
    $script = (string) app(SchemaBuilder::class)->toScript(['@type' => 'Thing']);

    expect($script)->toContain('<script type="application/ld+json">')
        ->toContain('"@type":"Thing"');
});
