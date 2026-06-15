<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Author = 'author';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Author => 'Author',
        };
    }

    /**
     * Roles that may moderate content site-wide (any author's posts,
     * categories, and comments).
     */
    public function managesContent(): bool
    {
        return $this === self::Admin || $this === self::Editor;
    }

    /**
     * Roles that may manage site configuration (settings, redirects, users).
     */
    public function managesSite(): bool
    {
        return $this === self::Admin;
    }
}
