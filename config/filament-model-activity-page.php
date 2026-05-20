<?php

return [
    /*
     * The Eloquent model used for activities.
     * Override in your app's config to use a custom model.
     */
    'activity_model' => \Djl997\FilamentModelActivityPage\Models\Activity::class,

    /*
     * Optional callback applied as a global scope on the Activity model
     * to filter out internal messages for non-privileged users.
     *
     * Example:
     *   'internal_scope_callback' => function (\Illuminate\Database\Eloquent\Builder $query) {
     *       if (auth()->check() && ! auth()->user()->isEchUser()) {
     *           $query->where('level', 'NOT LIKE', '%internal%');
     *       }
     *   },
     */
    'internal_scope_callback' => null,

    /*
     * Optional callback that returns true when the current user may see internal messages.
     * Only used when internal_scope_callback is null.
     */
    'can_see_internal_callback' => null,
];
