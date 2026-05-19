<?php

use Djl997\FilamentModelActivityPage\Enums\ActivityLevel;

describe('ActivityLevel::isInternal()', function () {
    it('returns false for non-internal levels', function (ActivityLevel $level) {
        expect($level->isInternal())->toBeFalse();
    })->with([
        ActivityLevel::Chat,
        ActivityLevel::Email,
        ActivityLevel::Info,
        ActivityLevel::Note,
    ]);

    it('returns true for internal levels', function (ActivityLevel $level) {
        expect($level->isInternal())->toBeTrue();
    })->with([
        ActivityLevel::InternalNote,
        ActivityLevel::InternalEmail,
        ActivityLevel::InternalInfo,
    ]);
});

describe('ActivityLevel::isInfo()', function () {
    it('returns true for info-type levels', function (ActivityLevel $level) {
        expect($level->isInfo())->toBeTrue();
    })->with([
        ActivityLevel::Info,
        ActivityLevel::InternalInfo,
        ActivityLevel::Email,
        ActivityLevel::InternalEmail,
    ]);

    it('returns false for non-info levels', function (ActivityLevel $level) {
        expect($level->isInfo())->toBeFalse();
    })->with([
        ActivityLevel::Chat,
        ActivityLevel::InternalNote,
        ActivityLevel::Note,
    ]);
});
