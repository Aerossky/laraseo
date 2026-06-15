<?php

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;

// --- Authors -----------------------------------------------------------------

it('lets an author create and publish their own post', function () {
    $author = User::factory()->author()->create();
    $category = Category::factory()->create();

    $this->actingAs($author)->post(route('admin.posts.store'), [
        'title' => 'My First Post',
        'content' => json_encode(['blocks' => []]),
        'status' => PostStatus::Published->value,
        'published_at' => now()->toDateTimeString(),
        'category_id' => $category->id,
    ])->assertRedirect(route('admin.posts.index'));

    $post = Post::sole();
    expect($post->author_id)->toBe($author->id)
        ->and($post->status)->toBe(PostStatus::Published);
});

it('shows an author only their own posts', function () {
    $author = User::factory()->author()->create();
    Post::factory()->create(['author_id' => $author->id, 'title' => 'Mine To See']);
    Post::factory()->create(['title' => 'Someone Elses Post']);

    $this->actingAs($author)->get(route('admin.posts.index'))
        ->assertOk()
        ->assertSee('Mine To See')
        ->assertDontSee('Someone Elses Post');
});

it('lets an author edit their own post but not another authors', function () {
    $author = User::factory()->author()->create();
    $own = Post::factory()->create(['author_id' => $author->id]);
    $foreign = Post::factory()->create();

    $this->actingAs($author)->get(route('admin.posts.edit', $own))->assertOk();
    $this->actingAs($author)->get(route('admin.posts.edit', $foreign))->assertForbidden();
    $this->actingAs($author)->delete(route('admin.posts.destroy', $foreign))->assertForbidden();
});

it('blocks authors from content management and site configuration', function () {
    $author = User::factory()->author()->create();

    $this->actingAs($author)->get(route('admin.categories.index'))->assertForbidden();
    $this->actingAs($author)->get(route('admin.comments.index'))->assertForbidden();
    $this->actingAs($author)->get(route('admin.redirects.index'))->assertForbidden();
    $this->actingAs($author)->get(route('admin.settings.index'))->assertForbidden();
    $this->actingAs($author)->get(route('admin.users.index'))->assertForbidden();
});

// --- Editors -----------------------------------------------------------------

it('lets an editor manage any authors post and the content sections', function () {
    $editor = User::factory()->editor()->create();
    $foreign = Post::factory()->create();

    $this->actingAs($editor)->get(route('admin.posts.edit', $foreign))->assertOk();
    $this->actingAs($editor)->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($editor)->get(route('admin.comments.index'))->assertOk();
});

it('blocks editors from site configuration', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('admin.redirects.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.settings.index'))->assertForbidden();
    $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
});

// --- Admins ------------------------------------------------------------------

it('lets an admin reach every section', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.redirects.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.settings.index'))->assertOk();
    $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
});
