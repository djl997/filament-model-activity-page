<?php

use Carbon\Carbon;
use Djl997\FilamentModelActivityPage\Data\ActivityMessage;
use Djl997\FilamentModelActivityPage\Tests\Fixtures\User;

it('sets computed by from user_name', function () {
    $msg = new ActivityMessage(message: 'Hello', user_name: 'Alice');

    expect($msg->by)->toBe('Alice');
});

it('sets by to empty string when user_name is null', function () {
    $msg = new ActivityMessage(message: 'Hello');

    expect($msg->by)->toBe('');
});

it('sets by_user to false when not authenticated', function () {
    $msg = new ActivityMessage(message: 'Hello', user_id: 1);

    expect($msg->by_user)->toBeFalse();
});

it('sets by_user to true when authenticated user matches', function () {
    $user = new User(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'secret']);
    $user->id = 5;
    $this->actingAs($user);

    $msg = new ActivityMessage(message: 'Hello', user_id: 5);

    expect($msg->by_user)->toBeTrue();
});

it('sets by_user to false when authenticated user does not match', function () {
    $user = new User(['name' => 'Alice', 'email' => 'alice@example.com', 'password' => 'secret']);
    $user->id = 5;
    $this->actingAs($user);

    $msg = new ActivityMessage(message: 'Hello', user_id: 99);

    expect($msg->by_user)->toBeFalse();
});

it('formats date as Y-m-d', function () {
    $at = Carbon::parse('2025-03-15 10:30:00');
    $msg = new ActivityMessage(message: 'Hello', created_at: $at);

    expect($msg->date)->toBe('2025-03-15');
});

it('uses now when created_at is null', function () {
    $before = now()->startOfSecond();
    $msg = new ActivityMessage(message: 'Hello');
    $after = now()->endOfSecond();

    expect($msg->created_at->between($before, $after))->toBeTrue();
});

it('toArray returns expected keys', function () {
    $msg = new ActivityMessage(message: 'Hello', user_name: 'Bob', level: 'chat', context: 'ctx');
    $arr = $msg->toArray();

    expect($arr)->toHaveKeys(['message', 'quoteMessage', 'user_name', 'user_id', 'by', 'by_user', 'level', 'created_at', 'date', 'time', 'context']);
    expect($arr['message'])->toBe('Hello');
    expect($arr['level'])->toBe('chat');
    expect($arr['context'])->toBe('ctx');
    expect($arr['quoteMessage'])->toBeNull();
});
