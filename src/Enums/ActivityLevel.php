<?php

namespace Djl997\FilamentModelActivityPage\Enums;

enum ActivityLevel: string
{
    case Chat = 'chat';
    case Event = 'event';

    public function isEvent(): bool
    {
        return $this === self::Event;
    }
}
