<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());
});

function uploadImage(string $name = 'photo.jpg'): Media
{
    test()->post(
        route('admin.media.upload'),
        ['file' => UploadedFile::fake()->image($name, 800, 600)],
        ['Accept' => 'application/json'],
    );

    return Media::query()->latest('id')->first();
}

it('requires authentication to access the media library', function () {
    auth()->logout();

    $this->get(route('admin.media.index'))->assertRedirect(route('login'));
});

it('shows the media library index', function () {
    $this->get(route('admin.media.index'))->assertOk();
});

it('uploads a valid image and returns an EditorJS response', function () {
    $response = $this->post(
        route('admin.media.upload'),
        ['file' => UploadedFile::fake()->image('My Photo.jpg', 800, 600)],
        ['Accept' => 'application/json'],
    );

    $response->assertOk()
        ->assertJsonPath('success', 1)
        ->assertJsonStructure(['file' => ['url', 'alt', 'id']]);

    expect(Media::where('collection_name', 'library')->count())->toBe(1);
});

it('rejects non-image uploads', function () {
    $this->post(
        route('admin.media.upload'),
        ['file' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')],
        ['Accept' => 'application/json'],
    )->assertStatus(422)
        ->assertJsonPath('success', 0)
        ->assertJsonValidationErrors('file');
});

it('rejects images larger than 5MB', function () {
    $this->post(
        route('admin.media.upload'),
        ['file' => UploadedFile::fake()->image('huge.jpg')->size(6000)],
        ['Accept' => 'application/json'],
    )->assertStatus(422)->assertJsonValidationErrors('file');
});

it('slugifies the stored file name', function () {
    $media = uploadImage('My Photo!.jpg');

    expect($media->file_name)->toStartWith('my-photo')
        ->and($media->file_name)->toEndWith('.jpg');
});

it('updates the alt text', function () {
    $media = uploadImage();

    $this->patch(route('admin.media.update', $media), ['alt' => 'A red bicycle'])
        ->assertRedirect();

    expect($media->fresh()->getCustomProperty('alt'))->toBe('A red bicycle');
});

it('deletes an unused image', function () {
    $media = uploadImage();

    $this->delete(route('admin.media.destroy', $media))->assertRedirect();

    expect(Media::find($media->id))->toBeNull();
});

it('blocks deleting an image that is referenced by a post', function () {
    $media = uploadImage();
    Post::factory()->create([
        'content' => ['blocks' => [['type' => 'image', 'data' => ['file' => ['url' => $media->getUrl()]]]]],
    ]);

    $this->delete(route('admin.media.destroy', $media))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Media::find($media->id))->not->toBeNull();
});

it('force-deletes a referenced image when confirmed', function () {
    $media = uploadImage();
    Post::factory()->create([
        'content' => ['blocks' => [['type' => 'image', 'data' => ['file' => ['url' => $media->getUrl()]]]]],
    ]);

    $this->delete(route('admin.media.destroy', $media), ['force' => true])->assertRedirect();

    expect(Media::find($media->id))->toBeNull();
});
