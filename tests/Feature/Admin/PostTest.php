<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function postData(array $overrides = []): array
{
    return array_merge([
        'title' => 'My First Post',
        'status' => 'draft',
        'content' => ['blocks' => []],
        'show_toc' => '1',
        'seo' => [],
    ], $overrides);
}

it('requires authentication for the posts area', function () {
    auth()->logout();

    $this->get(route('admin.posts.index'))->assertRedirect(route('login'));
});

it('lists posts', function () {
    Post::factory()->create(['title' => 'Hello Listing']);

    $this->get(route('admin.posts.index'))->assertOk()->assertSee('Hello Listing');
});

it('renders the create form', function () {
    $this->get(route('admin.posts.create'))->assertOk();
});

it('creates a draft post with a seo meta record', function () {
    $this->post(route('admin.posts.store'), postData(['seo' => ['meta_title' => 'Custom']]))
        ->assertRedirect(route('admin.posts.index'));

    $post = Post::first();

    expect($post->title)->toBe('My First Post')
        ->and($post->status)->toBe(PostStatus::Draft)
        ->and($post->slug)->toBe('my-first-post')
        ->and($post->seoMeta->meta_title)->toBe('Custom');
});

it('respects a manually provided slug', function () {
    $this->post(route('admin.posts.store'), postData(['slug' => 'custom-slug']));

    expect(Post::first()->slug)->toBe('custom-slug');
});

it('publishes a post with a category and stamps published_at', function () {
    $category = Category::factory()->create();

    $this->post(route('admin.posts.store'), postData([
        'status' => 'published',
        'category_id' => $category->id,
    ]))->assertRedirect(route('admin.posts.index'));

    $post = Post::first();

    expect($post->status)->toBe(PostStatus::Published)
        ->and($post->published_at)->not->toBeNull();
});

it('blocks publishing without a category', function () {
    $this->post(route('admin.posts.store'), postData(['status' => 'published']))
        ->assertSessionHasErrors('category_id');

    expect(Post::count())->toBe(0);
});

it('blocks publishing when an image block has no alt text', function () {
    $category = Category::factory()->create();

    $this->post(route('admin.posts.store'), postData([
        'status' => 'published',
        'category_id' => $category->id,
        'content' => ['blocks' => [
            ['type' => 'image', 'data' => ['file' => ['url' => '/storage/x.jpg'], 'caption' => '']],
        ]],
    ]))->assertSessionHasErrors('content');

    expect(Post::count())->toBe(0);
});

it('allows publishing when every image has alt text', function () {
    $category = Category::factory()->create();

    $this->post(route('admin.posts.store'), postData([
        'status' => 'published',
        'category_id' => $category->id,
        'content' => ['blocks' => [
            ['type' => 'image', 'data' => ['file' => ['url' => '/storage/x.jpg'], 'caption' => 'A photo']],
        ]],
    ]))->assertRedirect(route('admin.posts.index'));

    expect(Post::count())->toBe(1);
});

it('attaches a featured image with alt text', function () {
    Storage::fake('public');

    $this->post(route('admin.posts.store'), postData([
        'featured_image' => UploadedFile::fake()->image('hero.jpg'),
        'featured_alt' => 'Hero alt',
    ]));

    $post = Post::first();

    expect($post->getFirstMediaUrl('featured'))->not->toBe('')
        ->and($post->getFirstMedia('featured')->getCustomProperty('alt'))->toBe('Hero alt');
});

it('renders the edit form', function () {
    $post = Post::factory()->create();

    $this->get(route('admin.posts.edit', $post))->assertOk()->assertSee($post->title);
});

it('updates a post and its seo meta', function () {
    $post = Post::factory()->create(['title' => 'Old']);

    $this->put(route('admin.posts.update', $post), postData([
        'title' => 'Updated',
        'seo' => ['meta_title' => 'New SEO'],
    ]))->assertRedirect(route('admin.posts.index'));

    expect($post->fresh()->title)->toBe('Updated')
        ->and($post->fresh()->seoMeta->meta_title)->toBe('New SEO');
});

it('soft-deletes a post', function () {
    $post = Post::factory()->create();

    $this->delete(route('admin.posts.destroy', $post))->assertRedirect();

    expect(Post::count())->toBe(0)
        ->and(Post::withTrashed()->count())->toBe(1);
});

it('shows the dashboard with counts', function () {
    Post::factory()->count(2)->create();
    Post::factory()->published()->create();

    $this->get(route('admin.dashboard'))->assertOk()->assertSee('Total posts');
});
