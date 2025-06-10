<?php

declare(strict_types=1);

namespace App\Enums;

enum Visibility: string
{
    case Public = 'public';
    case Private = 'private';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Public->value => 'public',
            self::Private->value => 'private',
        ];
    }
}
