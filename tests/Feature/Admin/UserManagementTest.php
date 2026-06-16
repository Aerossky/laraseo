<?php

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('lets an admin create a user with a role and a hashed password', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'New Editor',
        'email' => 'neweditor@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'role' => UserRole::Editor->value,
    ])->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'neweditor@example.com')->sole();
    expect($user->role)->toBe(UserRole::Editor)
        ->and($user->name)->toBe('New Editor')
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

it('keeps the existing password when the field is left blank on update', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->editor()->create(['password' => Hash::make('original-password')]);

    $this->actingAs($admin)->put(route('admin.users.update', $target), [
        'name' => 'Renamed Editor',
        'email' => $target->email,
        'password' => '',
        'password_confirmation' => '',
        'role' => UserRole::Author->value,
    ])->assertRedirect(route('admin.users.index'));

    $target->refresh();
    expect($target->name)->toBe('Renamed Editor')
        ->and($target->role)->toBe(UserRole::Author)
        ->and(Hash::check('original-password', $target->password))->toBeTrue();
});

it('stops an admin from demoting their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => UserRole::Author->value,
    ])->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe(UserRole::Admin);
});

it('stops an admin from deleting their own account', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();

    expect(User::find($admin->id))->not->toBeNull();
});

it('lets an admin delete another user while keeping their posts', function () {
    $admin = User::factory()->admin()->create();
    $author = User::factory()->author()->create();
    $post = Post::factory()->create(['author_id' => $author->id]);

    $this->actingAs($admin)->delete(route('admin.users.destroy', $author))->assertRedirect();

    expect(User::find($author->id))->toBeNull()
        ->and($post->fresh()->author_id)->toBeNull();
});

it('forbids non-admins from creating users', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->post(route('admin.users.store'), [
        'name' => 'Sneaky',
        'email' => 'sneaky@example.com',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'role' => UserRole::Admin->value,
    ])->assertForbidden();

    expect(User::where('email', 'sneaky@example.com')->exists())->toBeFalse();
});
