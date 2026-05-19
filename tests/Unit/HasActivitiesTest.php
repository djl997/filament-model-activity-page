<?php

use Djl997\FilamentModelActivityPage\Models\Activity;
use Djl997\FilamentModelActivityPage\Tests\Fixtures\Post;
use Illuminate\Database\Eloquent\Relations\MorphMany;

it('provides an activities() morphMany relation', function () {
    $post = new Post;

    expect($post->activities())->toBeInstanceOf(MorphMany::class);
});

it('activities() relation points to the configured activity model', function () {
    $post = new Post;

    expect($post->activities()->getRelated())->toBeInstanceOf(Activity::class);
});

it('activities() uses a custom model from config', function () {
    config()->set('filament-model-activity-page.activity_model', Activity::class);

    $post = new Post;

    expect($post->activities()->getRelated())->toBeInstanceOf(Activity::class);
});

it('creates and retrieves activities through the relation', function () {
    $post = Post::create(['title' => 'Test Post']);

    $post->activities()->withoutGlobalScopes()->create([
        'message' => 'First activity',
        'level' => 'chat',
    ]);

    expect($post->activities()->withoutGlobalScopes()->count())->toBe(1);
    expect($post->activities()->withoutGlobalScopes()->first()->message)->toBe('First activity');
});
