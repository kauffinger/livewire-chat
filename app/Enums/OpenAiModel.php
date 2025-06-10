<?php

namespace App\Enums;

enum OpenAiModel: string
{
    case Gpt3_5Turbo = 'gpt-3.5-turbo';
    case Gpt4 = 'gpt-4';
    case Gpt4oMini = 'gpt-4o-mini';
    case Gpt4o = 'gpt-4o';

    case o3 = 'o3-2025-04-16';

    case o4Mini = 'o4-mini-2025-04-16';

    case Gpt41nano = 'gpt-4.1-nano-2025-04-14';

    /**
     * Get the values of the enum.
     *
     * @return array<string, string>
     */
    public static function toArray(): array
    {
        return [
            self::Gpt3_5Turbo->value => 'gpt-3.5-turbo',
            self::Gpt4->value => 'gpt-4',
            self::Gpt4oMini->value => 'gpt-4o-mini',
            self::Gpt4o->value => 'gpt-4o',
            self::o3->value => 'o3-2025-04-16',
            self::o4Mini->value => 'o4-mini-2025-04-16',
            self::Gpt41nano->value => 'gpt-4.1-nano-2025-04-14',
        ];
    }
}
