<?php

use Djl997\FilamentModelActivityPage\Enums\ActivityLevel;

describe('ActivityLevel::isEvent()', function () {
    it('returns true for Event', function () {
        expect(ActivityLevel::Event->isEvent())->toBeTrue();
    });

    it('returns false for Chat', function () {
        expect(ActivityLevel::Chat->isEvent())->toBeFalse();
    });
});
