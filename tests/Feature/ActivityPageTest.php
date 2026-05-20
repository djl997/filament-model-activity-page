<?php

use Djl997\FilamentModelActivityPage\Models\Activity;
use Djl997\FilamentModelActivityPage\Tests\Fixtures\Post;
use Djl997\FilamentModelActivityPage\Tests\Fixtures\PostNotesPage;
use Djl997\FilamentModelActivityPage\Tests\Fixtures\User;

beforeEach(function () {
    $this->user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('secret'),
    ]);

    $this->post = Post::create(['title' => 'Test Post']);

    $this->actingAs($this->user);
});

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makePage(Post $post, bool $privileged = false, bool $allowInternal = false): PostNotesPage
{
    $page = new PostNotesPage;
    $page->record = $post;
    $page->privileged = $privileged;
    $page->allowInternal = $allowInternal;

    return $page;
}

// ---------------------------------------------------------------------------
// getChatMessages
// ---------------------------------------------------------------------------

it('getChatMessages returns an empty collection when there are no activities', function () {
    $page = makePage($this->post);

    expect($page->getChatMessages())->toBeEmpty();
});

it('getChatMessages returns activities after one is created', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Hello world',
        'level' => 'chat',
    ]);

    expect(makePage($this->post)->getChatMessages())->toHaveCount(1);
});

// ---------------------------------------------------------------------------
// getGroupedChatMessages
// ---------------------------------------------------------------------------

it('getGroupedChatMessages groups consecutive messages by the same author', function () {
    $now = now();

    $this->post->activities()->withoutGlobalScopes()->createMany([
        ['user_id' => $this->user->id, 'message' => 'First', 'level' => 'chat', 'created_at' => $now],
        ['user_id' => $this->user->id, 'message' => 'Second', 'level' => 'chat', 'created_at' => $now->copy()->addSecond()],
    ]);

    $grouped = makePage($this->post)->getGroupedChatMessages();

    expect($grouped)->toHaveCount(1);
    expect($grouped[0]['groups'])->toHaveCount(1);
    expect($grouped[0]['groups'][0]['messages'])->toHaveCount(2);
});

it('getGroupedChatMessages separates info messages into their own groups', function () {
    $now = now();

    $this->post->activities()->withoutGlobalScopes()->createMany([
        ['user_id' => $this->user->id, 'message' => 'Chat msg', 'level' => 'chat', 'created_at' => $now],
        ['user_id' => null, 'message' => 'System info', 'level' => 'info', 'created_at' => $now->copy()->addSecond()],
    ]);

    $grouped = makePage($this->post)->getGroupedChatMessages();

    expect($grouped[0]['groups'])->toHaveCount(2);
    expect($grouped[0]['groups'][0]['is_info'])->toBeFalse();
    expect($grouped[0]['groups'][1]['is_info'])->toBeTrue();
});

// ---------------------------------------------------------------------------
// canChat
// ---------------------------------------------------------------------------

it('canChat returns true when there are no previous messages', function () {
    expect(makePage($this->post)->canChat())->toBeTrue();
});

it('canChat returns false after a non-privileged user posts within 60 seconds', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Hello',
        'level' => 'chat',
        'created_at' => now(),
    ]);

    expect(makePage($this->post)->canChat())->toBeFalse();
});

it('canChat returns true for privileged users regardless of rate limit', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Hello',
        'level' => 'chat',
        'created_at' => now(),
    ]);

    expect(makePage($this->post, privileged: true)->canChat())->toBeTrue();
});

// ---------------------------------------------------------------------------
// sendMessage
// ---------------------------------------------------------------------------

it('sendMessage creates a chat activity in the database', function () {
    $page = makePage($this->post);
    $page->data = ['message' => 'My first message', 'internal' => false];

    $page->sendMessage();

    expect(Activity::withoutGlobalScopes()->where('activitable_id', $this->post->id)->count())->toBe(1);
    expect(Activity::withoutGlobalScopes()->first()->message)->toBe('My first message');
    expect(Activity::withoutGlobalScopes()->first()->level->value)->toBe('chat');
});

it('sendMessage clears the message field after posting', function () {
    $page = makePage($this->post);
    $page->data = ['message' => 'Hello', 'internal' => false];

    $page->sendMessage();

    expect($page->data['message'])->toBe('');
});

it('sendMessage is blocked for non-privileged users within the rate-limit window', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Already sent',
        'level' => 'chat',
        'created_at' => now(),
    ]);

    $page = makePage($this->post);
    $page->data = ['message' => 'Second message', 'internal' => false];

    $page->sendMessage();

    expect(Activity::withoutGlobalScopes()->count())->toBe(1);
});

it('sendMessage is allowed for privileged users even within the rate-limit window', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'First',
        'level' => 'chat',
        'created_at' => now(),
    ]);

    $page = makePage($this->post, privileged: true);
    $page->data = ['message' => 'Second', 'internal' => false];

    $page->sendMessage();

    expect(Activity::withoutGlobalScopes()->count())->toBe(2);
});

it('sendMessage stores internal_note when the user can send internal messages and checks the box', function () {
    $page = makePage($this->post, allowInternal: true);
    $page->data = ['message' => 'Internal note', 'internal' => true];

    $page->sendMessage();

    expect(Activity::withoutGlobalScopes()->first()->level->value)->toBe('internal_note');
});

it('sendMessage stores chat level even when internal is true but canSendInternalMessages is false', function () {
    $page = makePage($this->post);
    $page->data = ['message' => 'Should be chat', 'internal' => true];

    $page->sendMessage();

    expect(Activity::withoutGlobalScopes()->first()->level->value)->toBe('chat');
});

// ---------------------------------------------------------------------------
// getChildActivities
// ---------------------------------------------------------------------------

it('getChatMessages merges child activities with context into the feed', function () {
    $this->post->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Parent message',
        'level' => 'chat',
    ]);

    $child = Post::create(['title' => 'Child Post']);
    $child->activities()->withoutGlobalScopes()->create([
        'user_id' => $this->user->id,
        'message' => 'Child message',
        'level' => 'chat',
    ]);

    $page = makePage($this->post);
    $page->childActivityEntries = [
        ['activities' => $child->activities()->withoutGlobalScopes()->get(), 'context' => 'Child Post'],
    ];

    $messages = $page->getChatMessages();

    expect($messages)->toHaveCount(2);

    $childMsg = $messages->firstWhere('message', 'Child message');
    expect($childMsg['context'])->toBe('Child Post');

    $parentMsg = $messages->firstWhere('message', 'Parent message');
    expect($parentMsg['context'])->toBeNull();
});
