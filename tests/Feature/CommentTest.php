<?php

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

it('lets a guest submit a comment that lands in the moderation queue', function () {
    $post = Post::factory()->published()->create();

    $this->post(route('blog.comments.store', $post), [
        'author_name' => 'Jane Guest',
        'author_email' => 'jane@example.com',
        'body' => 'Great article, thanks!',
        'website' => '',
    ])->assertRedirect();

    $comment = Comment::sole();
    expect($comment->status)->toBe(CommentStatus::Pending)
        ->and($comment->user_id)->toBeNull()
        ->and($comment->author_name)->toBe('Jane Guest')
        ->and($comment->post_id)->toBe($post->id);
});

it('records an authenticated user as the author', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $this->actingAs($user)->post(route('blog.comments.store', $post), [
        'body' => 'Signed in and commenting.',
    ])->assertRedirect();

    $comment = Comment::sole();
    expect($comment->user_id)->toBe($user->id)
        ->and($comment->author_name)->toBeNull();
});

it('shows approved comments on the post but hides pending ones', function () {
    $post = Post::factory()->published()->create();
    Comment::factory()->approved()->for($post)->create(['author_name' => 'Visible Author', 'body' => 'Approved body']);
    Comment::factory()->for($post)->create(['author_name' => 'Hidden Author', 'body' => 'Pending body']);

    $this->get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('Approved body')
        ->assertDontSee('Pending body');
});

it('requires a name and email from guests', function () {
    $post = Post::factory()->published()->create();

    $this->post(route('blog.comments.store', $post), [
        'body' => 'No identity given.',
        'website' => '',
    ])->assertSessionHasErrors(['author_name', 'author_email']);

    expect(Comment::count())->toBe(0);
});

it('requires a body', function () {
    $post = Post::factory()->published()->create();

    $this->post(route('blog.comments.store', $post), [
        'author_name' => 'Jane',
        'author_email' => 'jane@example.com',
        'body' => '   ',
    ])->assertSessionHasErrors('body');
});

it('rejects submissions that trip the honeypot', function () {
    $post = Post::factory()->published()->create();

    $this->post(route('blog.comments.store', $post), [
        'author_name' => 'Spam Bot',
        'author_email' => 'bot@example.com',
        'body' => 'Buy cheap stuff',
        'website' => 'http://spam.example',
    ])->assertSessionHasErrors('website');

    expect(Comment::count())->toBe(0);
});

it('does not accept comments on a draft post', function () {
    $draft = Post::factory()->create();

    $this->post(route('blog.comments.store', $draft), [
        'author_name' => 'Jane',
        'author_email' => 'jane@example.com',
        'body' => 'Should not work',
    ])->assertNotFound();
});

it('lets an admin approve a pending comment so it becomes public', function () {
    $admin = User::factory()->create();
    $comment = Comment::factory()->create(['body' => 'Please approve me']);

    $this->actingAs($admin)
        ->patch(route('admin.comments.approve', $comment))
        ->assertRedirect();

    expect($comment->fresh()->status)->toBe(CommentStatus::Approved)
        ->and($comment->fresh()->approved_at)->not->toBeNull();
});

it('lets an admin mark a comment as spam and delete it', function () {
    $admin = User::factory()->create();
    $comment = Comment::factory()->approved()->create();

    $this->actingAs($admin)->patch(route('admin.comments.spam', $comment))->assertRedirect();
    expect($comment->fresh()->status)->toBe(CommentStatus::Spam);

    $this->actingAs($admin)->delete(route('admin.comments.destroy', $comment))->assertRedirect();
    expect(Comment::find($comment->id))->toBeNull();
});

it('blocks guests from the admin moderation screen', function () {
    $this->get(route('admin.comments.index'))->assertRedirect(route('login'));
});
