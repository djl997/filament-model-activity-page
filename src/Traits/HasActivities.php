<?php

namespace Djl997\FilamentModelActivityPage\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivities
{
    public function activities(): MorphMany
    {
        $model = config(
            'filament-model-activity-page.activity_model',
            \Djl997\FilamentModelActivityPage\Models\Activity::class
        );

        return $this->morphMany($model, 'activitable');
    }
}
