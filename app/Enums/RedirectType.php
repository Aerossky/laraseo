<?php

namespace App\Enums;

enum RedirectType: int
{
    case Permanent = 301;
    case Temporary = 302;

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent (301)',
            self::Temporary => 'Temporary (302)',
        };
    }
}
