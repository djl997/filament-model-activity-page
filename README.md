# filament-model-activity-page

A reusable Filament v5 page that renders a chat-style activity feed for any Eloquent model with a polymorphic `activities` relation.

## Requirements

- PHP 8.2+
- Laravel 12+
- Filament 5+

## Installation

```bash
composer require djl997/filament-model-activity-page
```

### 1. Publish and run the migration

```bash
php artisan vendor:publish --tag=filament-model-activity-page-migrations
php artisan migrate
```

### 2. Add the trait to your model

```php
use Djl997\FilamentModelActivityPage\Traits\HasActivities;

class Company extends Model
{
    use HasActivities;
}
```

### 3. Create a Filament page

```php
use Djl997\FilamentModelActivityPage\Pages\ActivityPage;

class Notes extends ActivityPage
{
    protected static string $resource = CompanyResource::class;

    protected function isPrivilegedUser(): bool
    {
        return auth()->user()->isAdmin();
    }

    protected function canSendInternalMessages(): bool
    {
        return auth()->user()->isAdmin();
    }

    protected function afterClientMessage(array $data): void
    {
        // Send notification, fire event, etc.
    }
}
```

### 4. Register the page in your resource

```php
public static function getPages(): array
{
    return [
        // ...
        'notes' => Notes::route('/{record}/notes'),
    ];
}
```

### 5. Add the package views to your Filament theme

In `resources/css/filament/app/theme.css`:

```css
@source '../../../../vendor/djl997/filament-model-activity-page/resources/views/**/*';
```

Then rebuild: `npm run build`

## Override points

| Method | Default | Purpose |
|---|---|---|
| `getEagerLoadRelations()` | `['activities.user']` | Add extra eager-load chains |
| `getChatMessages()` | loads `activities` on record | Override to aggregate from related models |
| `isPrivilegedUser()` | `false` | Bypasses rate limiting |
| `canSendInternalMessages()` | `false` | Shows the internal checkbox on the compose form |
| `afterClientMessage(array $data)` | no-op | Send notifications after a client posts |
| `resolveActivityIcon(array $activity)` | `null` | Return a Heroicon name to show an icon on any activity |

### Adding icons to activities

Override `resolveActivityIcon()` in your page class to return a Heroicon name for a given activity. The `$activity` array contains all formatted fields including `level`, `is_internal`, `message`, etc.

```php
protected function resolveActivityIcon(array $activity): ?string
{
    // Show an envelope icon on event activities that contain "email" in the message
    if ($activity['level'] === 'event' && str_contains($activity['message'], 'email')) {
        return 'heroicon-m-envelope';
    }

    return null;
}
```

Icons are rendered on info-bar (Event) activities only. Chat bubbles do not show icons.

## Configuration

Publish the config to swap in your own Activity model or customise internal message visibility:

```bash
php artisan vendor:publish --tag=filament-model-activity-page-config
```

```php
// config/filament-model-activity-page.php
return [
    'activity_model' => App\Models\Activity::class,

    'internal_scope_callback' => function (\Illuminate\Database\Eloquent\Builder $query) {
        if (auth()->check() && ! auth()->user()->isAdmin()) {
            $query->where('is_internal', false);
        }
    },
];
```

## Panel plugin registration (optional)

If you want to register the plugin explicitly with a Filament panel:

```php
use Djl997\FilamentModelActivityPage\FilamentModelActivityPagePlugin;

->plugins([
    FilamentModelActivityPagePlugin::make(),
])
```
