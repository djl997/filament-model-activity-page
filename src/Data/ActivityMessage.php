<?php

namespace Djl997\FilamentModelActivityPage\Data;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ActivityMessage
{
    public readonly string $by;

    public readonly bool $by_user;

    public readonly string $date;

    public readonly Carbon $created_at;

    public function __construct(
        public readonly string $message,
        public readonly ?string $user_name = null,
        public readonly mixed $user_id = null,
        ?Carbon $created_at = null,
        public readonly string $level = 'chat',
        public readonly bool $is_internal = false,
        public readonly ?string $context = null,
    ) {
        $this->created_at = $created_at ?? now();
        $this->by = $this->user_name ?? '';
        $this->by_user = Auth::check() && $this->user_id !== null && $this->user_id === Auth::id();
        $this->date = $this->created_at->format('Y-m-d');
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'quoteMessage' => null,
            'user_name' => $this->user_name,
            'user_id' => $this->user_id,
            'by' => $this->by,
            'by_user' => $this->by_user,
            'level' => $this->level,
            'is_internal' => $this->is_internal,
            'created_at' => $this->created_at->toString(),
            'date' => $this->date,
            'time' => $this->created_at->timestamp,
            'context' => $this->context,
        ];
    }
}
