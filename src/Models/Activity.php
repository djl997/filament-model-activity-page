<?php

namespace Djl997\FilamentModelActivityPage\Models;

use Djl997\FilamentModelActivityPage\Enums\ActivityLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class Activity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'message',
        'level',
        'activitable_id',
        'activitable_type',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'level' => ActivityLevel::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('internal', function (Builder $builder) {
            $callback = config('filament-model-activity-page.internal_scope_callback');

            if ($callback && ! app()->runningInConsole()) {
                $callback($builder);
            } elseif (! app()->runningInConsole()) {
                $builder->where(function ($query) {
                    if (Auth::check()) {
                        $canSeeInternal = config(
                            'filament-model-activity-page.can_see_internal_callback',
                            fn () => false
                        );
                        if (! $canSeeInternal()) {
                            $query->where('level', 'NOT LIKE', '%internal%');
                        }
                    } else {
                        $query->where('level', 'NOT LIKE', '%internal%');
                    }
                });
            }
        });
    }

    public function activitable(): MorphTo
    {
        return $this->morphTo()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class));
    }
}
