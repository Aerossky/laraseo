<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['name', 'email', 'password', 'role', 'bio', 'website', 'twitter', 'linkedin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, InteractsWithMedia, Notifiable;

    /** The media collection holding the user's avatar. */
    public const AVATAR_COLLECTION = 'avatar';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::AVATAR_COLLECTION)
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function getAvatarUrl(): ?string
    {
        $url = $this->getFirstMediaUrl(self::AVATAR_COLLECTION);

        return $url !== '' ? $url : null;
    }

    /**
     * The author's social links, keyed by network, filtered to those that are set.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        return array_filter([
            'website' => $this->website,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
        ]);
    }

    /** @return HasMany<Post, $this> */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isEditor(): bool
    {
        return $this->role === UserRole::Editor;
    }

    public function isAuthor(): bool
    {
        return $this->role === UserRole::Author;
    }

    /**
     * May moderate any author's content (posts, categories, comments).
     */
    public function managesContent(): bool
    {
        return $this->role->managesContent();
    }

    /**
     * May manage site configuration (settings, redirects, users).
     */
    public function managesSite(): bool
    {
        return $this->role->managesSite();
    }
}
