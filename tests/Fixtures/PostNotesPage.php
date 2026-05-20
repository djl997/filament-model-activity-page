<?php

namespace Djl997\FilamentModelActivityPage\Tests\Fixtures;

use Djl997\FilamentModelActivityPage\Pages\ActivityPage;

class PostNotesPage extends ActivityPage
{
    protected static string $resource = PostResource::class;

    public bool $privileged = false;

    public bool $allowInternal = false;

    public array $childActivityEntries = [];

    protected function isPrivilegedUser(): bool
    {
        return $this->privileged;
    }

    protected function canSendInternalMessages(): bool
    {
        return $this->allowInternal;
    }

    protected function getChildActivities(): array
    {
        return $this->childActivityEntries;
    }
}
