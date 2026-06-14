<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

beforeEach(fn () => $this->actingAs(User::factory()->create()));

function categoryData(array $overrides = []): array
{
    return array_merge([
        'name' => 'News',
        'description' => 'Latest updates',
        'seo' => [],
    ], $overrides);
}

it('requires authentication for the categories area', function () {
    auth()->logout();

    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
});

it('lists categories with their post counts', function () {
    $category = Category::factory()->create(['name' => 'Tutorials']);
    Post::factory()->count(2)->create(['category_id' => $category->id]);

    $this->get(route('admin.categories.index'))->assertOk()->assertSee('Tutorials');
});

it('renders the create form', function () {
    $this->get(route('admin.categories.create'))->assertOk();
});

it('creates a category with a seo meta record', function () {
    $this->post(route('admin.categories.store'), categoryData(['seo' => ['meta_title' => 'Custom']]))
        ->assertRedirect(route('admin.categories.index'));

    $category = Category::first();

    expect($category->name)->toBe('News')
        ->and($category->slug)->toBe('news')
        ->and($category->seoMeta->meta_title)->toBe('Custom');
});

it('respects a manually provided slug', function () {
    $this->post(route('admin.categories.store'), categoryData(['slug' => 'custom-slug']));

    expect(Category::first()->slug)->toBe('custom-slug');
});

it('requires a name', function () {
    $this->post(route('admin.categories.store'), categoryData(['name' => '']))
        ->assertSessionHasErrors('name');

    expect(Category::count())->toBe(0);
});

it('rejects a duplicate slug', function () {
    Category::factory()->create(['slug' => 'taken']);

    $this->post(route('admin.categories.store'), categoryData(['slug' => 'taken']))
        ->assertSessionHasErrors('slug');
});

it('renders the edit form', function () {
    $category = Category::factory()->create();

    $this->get(route('admin.categories.edit', $category))->assertOk()->assertSee($category->name);
});

it('updates a category and its seo meta', function () {
    $category = Category::factory()->create(['name' => 'Old']);

    $this->put(route('admin.categories.update', $category), categoryData([
        'name' => 'Updated',
        'seo' => ['meta_title' => 'New SEO'],
    ]))->assertRedirect(route('admin.categories.index'));

    expect($category->fresh()->name)->toBe('Updated')
        ->and($category->fresh()->seoMeta->meta_title)->toBe('New SEO');
});

it('keeps a manually edited slug stable when the name changes', function () {
    $category = Category::factory()->create(['slug' => 'stable']);

    $this->put(route('admin.categories.update', $category), categoryData(['name' => 'Renamed']));

    expect($category->fresh()->slug)->toBe('stable');
});

it('deletes a category and uncategorizes its posts', function () {
    $category = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    $this->delete(route('admin.categories.destroy', $category))->assertRedirect();

    expect(Category::count())->toBe(0)
        ->and($post->fresh()->category_id)->toBeNull();
});
