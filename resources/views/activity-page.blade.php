<x-filament-panels::page>
    <div
        class="flex flex-col gap-4"
        wire:poll.10s.visible
    >
        <x-filament::section>
            {{-- Messages --}}
            <div
                class="flex flex-col-reverse gap-1 max-h-125 overflow-y-auto no-scrollbar"
                x-data
                x-on:message-sent.window="$el.scrollTop = 0"
            >
                @php $days = $this->getGroupedChatMessages(); @endphp

                @forelse ($days as $day)
                    {{-- Day block (reversed, so rendered bottom-up) --}}
                    <div class="flex flex-col gap-2">

                        {{-- Date divider --}}
                        <div class="flex items-center gap-3 my-3 select-none">
                            <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 shrink-0">{{ $day['date'] }}</span>
                            <div class="flex-1 border-t border-gray-200 dark:border-white/10"></div>
                        </div>

                        {{-- Author groups --}}
                        @foreach ($day['groups'] as $group)
                            @if ($group['is_info'])
                                {{-- Info: centered pill, no author/time --}}
                                <div class="flex flex-col items-center gap-1">
                                    @foreach ($group['messages'] as $message)
                                        @php
                                            $isInternal = $message['is_internal'];
                                            $icon = $message['icon'] ?? null;
                                        @endphp
                                        <div @class([
                                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs max-w-[80%] wrap-break-word text-center',
                                            'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400' => $isInternal,
                                            'bg-primary-50 text-primary-600 dark:bg-primary-900/30 dark:text-primary-300' => ! $isInternal,
                                        ])>
                                            @if ($icon)
                                                <x-filament::icon :icon="$icon" class="size-3 shrink-0" />
                                            @endif
                                            {{ $message['message'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                {{-- Chat group --}}
                                <div @class([
                                    'flex flex-col mb-2',
                                    'items-end' => $group['is_mine'],
                                    'items-start' => ! $group['is_mine'],
                                ])>
                                    {{-- Author + time (only once per group) --}}
                                    <div class="flex items-center gap-1.5 mb-1 text-xs text-gray-400 dark:text-gray-500 select-none">
                                        @if (! $group['is_mine'])
                                            <span class="font-medium text-gray-600 dark:text-gray-300">{{ $group['author'] }}</span>
                                        @endif
                                        <span>{{ $group['time'] }}</span>
                                    </div>

                                    {{-- Bubbles --}}
                                    <div @class([
                                        'flex flex-col gap-0.5 max-w-[80%]',
                                        'items-end' => $group['is_mine'],
                                        'items-start' => ! $group['is_mine'],
                                    ])>
                                        @foreach ($group['messages'] as $message)
                                            @php $isInternal = $message['is_internal']; @endphp
                                            <div @class([
                                                'flex items-start gap-1.5 rounded-lg px-3 py-2 text-sm',
                                                'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200' => $isInternal,
                                                'bg-primary-600 text-white' => ! $isInternal && $group['is_mine'],
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' => ! $isInternal && ! $group['is_mine'],
                                            ])>
                                                @if ($isInternal)
                                                    <x-filament::icon icon="heroicon-m-eye-slash" class="size-3.5 mt-0.5 shrink-0 opacity-60" />
                                                @endif
                                                {{ $message['message'] }}
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        {{ __('filament-model-activity-page::activity-page.no_messages') }}
                    </p>
                @endforelse
            </div>

            <div class="my-4 -mx-6">
                <hr class="border-gray-300 dark:border-white/10" />
            </div>

            {{-- Compose form --}}
            @php $canChat = $this->canChat(); @endphp

            @if (! $canChat)
            <p class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                <x-filament::icon icon="heroicon-m-information-circle" class="size-4 shrink-0" />
                {{ __('filament-model-activity-page::activity-page.rate_limit_message') }}
            </p>
            @else
            <form wire:submit="sendMessage" class="flex gap-3">
                <div class="grow">
                    {{ $this->form }}
                </div>

                <div class="shrink-0">
                    <x-filament::button
                        type="submit"
                        icon="heroicon-m-paper-airplane"
                        :disabled="! $canChat"
                    >
                        {{ __('filament-model-activity-page::activity-page.send_button') }}
                    </x-filament::button>
                </div>
            </form>
            @endif

        </x-filament::section>
    </div>

    <x-filament-actions::modals />
</x-filament-panels::page>
