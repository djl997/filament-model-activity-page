<?php

namespace Djl997\FilamentModelActivityPage\Enums;

enum ActivityLevel: string
{
    case Chat = 'chat';
    case InternalNote = 'internal_note';
    case Email = 'email';
    case InternalEmail = 'internal_email';
    case Info = 'info';
    case InternalInfo = 'internal_info';
    case Note = 'note';

    public function isInternal(): bool
    {
        return str_contains($this->value, 'internal');
    }

    public function isInfo(): bool
    {
        return in_array($this, [self::Info, self::InternalInfo, self::Email, self::InternalEmail]);
    }
}
