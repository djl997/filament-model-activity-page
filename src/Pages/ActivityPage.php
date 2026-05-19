<?php

namespace Djl997\FilamentModelActivityPage\Pages;

use BackedEnum;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

abstract class ActivityPage extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithRecord;

    protected string $view = 'filament-model-activity-page::activity-page';

    public ?array $data = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->form->fill([
            'internal' => $this->canSendInternalMessages(),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                TextInput::make('message')
                    ->hiddenLabel()
                    ->placeholder(__('filament-model-activity-page::activity-page.placeholder'))
                    ->maxLength(1000)
                    ->required(),

                Checkbox::make('internal')
                    ->label(__('filament-model-activity-page::activity-page.internal_label'))
                    ->helperText(__('filament-model-activity-page::activity-page.internal_helper'))
                    ->visible(fn () => $this->canSendInternalMessages()),
            ])
            ->statePath('data');
    }

    public function sendMessage(): void
    {
        if (! $this->canChat()) {
            Notification::make()
                ->warning()
                ->title(__('filament-model-activity-page::activity-page.rate_limit_message'))
                ->send();

            return;
        }

        $data = $this->form->getState();

        $isInternal = ($data['internal'] ?? false) && $this->canSendInternalMessages();
        $level = $isInternal ? 'internal_note' : 'chat';

        $this->getRecord()->activities()->create([
            'user_id' => auth()->id(),
            'message' => $data['message'],
            'level' => $level,
        ]);

        if (! $this->isPrivilegedUser()) {
            $this->afterClientMessage($data);
        }

        $this->form->fill([
            'message' => '',
            'internal' => $data['internal'] ?? false,
        ]);

        $this->dispatch('message-sent');
    }

    public function canChat(): bool
    {
        if ($this->isPrivilegedUser()) {
            return true;
        }

        $lastUserMessage = $this->getChatMessages()
            ->filter(fn ($m) => $m['by_current_user'])
            ->max('timestamp');

        return ($lastUserMessage === null) || (time() - $lastUserMessage > 60);
    }

    public function getChatMessages(): Collection
    {
        $record = $this->getRecord()->load($this->getEagerLoadRelations());

        $messages = collect();

        foreach ($record->activities as $activity) {
            $messages->push($this->formatActivity($activity));
        }

        return $messages->sortByDesc('timestamp');
    }

    /**
     * Returns messages grouped by date, then by consecutive author runs.
     * Structure: [ ['date' => '...', 'groups' => [ ['author' => '...', 'is_mine' => bool, 'messages' => [...]] ]] ]
     * Ordered oldest-first within each day; days in descending order so flex-col-reverse renders correctly.
     */
    public function getGroupedChatMessages(): array
    {
        $messages = $this->getChatMessages()->sortBy('timestamp')->values();

        $byDate = $messages->groupBy(fn ($m) => $m['date_only']);

        $days = [];

        foreach ($byDate as $date => $dayMessages) {
            $groups = [];
            $currentGroup = null;

            foreach ($dayMessages as $message) {
                $isInfo = in_array($message['level'], ['info', 'internal_info', 'email', 'internal_email']);
                $groupKey = $isInfo ? '__info__' : ($message['by_current_user'] ? '__mine__' : $message['author']);

                if ($currentGroup === null || $currentGroup['key'] !== $groupKey || $isInfo || $currentGroup['is_info']) {
                    if ($currentGroup !== null) {
                        $groups[] = $currentGroup;
                    }
                    $currentGroup = [
                        'key' => $groupKey,
                        'author' => $message['author'],
                        'is_mine' => $message['by_current_user'],
                        'is_info' => $isInfo,
                        'time' => $message['time'],
                        'messages' => [],
                    ];
                }

                $currentGroup['messages'][] = $message;
            }

            if ($currentGroup !== null) {
                $groups[] = $currentGroup;
            }

            $days[] = [
                'date' => $date,
                'groups' => $groups,
            ];
        }

        return array_reverse($days);
    }

    protected function formatActivity(\Illuminate\Database\Eloquent\Model $activity, ?string $context = null): array
    {
        $level = $activity->level instanceof \BackedEnum ? $activity->level->value : ($activity->level ?? '');
        $isInternal = str_contains($level, 'internal');

        return [
            'id' => $activity->id,
            'message' => $activity->message,
            'author' => optional($activity->user)->name ?? __('filament-model-activity-page::activity-page.system_author'),
            'by_current_user' => $activity->user_id === auth()->id(),
            'timestamp' => $activity->created_at?->timestamp,
            'date' => $activity->created_at?->format('d-m-Y H:i'),
            'date_only' => $activity->created_at?->format('d-m-Y'),
            'time' => $activity->created_at?->format('H:i'),
            'is_internal' => $isInternal,
            'context' => $context,
            'level' => $level,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    public static function getNavigationIcon(): string|BackedEnum|Htmlable|null
    {
        return null;
    }

    // --- Override points ---

    /** Eager-load relations for getChatMessages(). Override to add project-specific chains. */
    protected function getEagerLoadRelations(): array
    {
        return ['activities.user'];
    }

    /** Return true for users who bypass the rate limit and can mark messages as internal. */
    protected function isPrivilegedUser(): bool
    {
        return false;
    }

    /** Return true to show the internal-message checkbox. */
    protected function canSendInternalMessages(): bool
    {
        return false;
    }

    /** Called after a non-privileged user sends a message. Override to send notifications. */
    protected function afterClientMessage(array $data): void {}
}
