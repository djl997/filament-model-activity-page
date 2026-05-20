# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer test                    # run all tests
./vendor/bin/pest tests/Unit/ActivityMessageTest.php  # run a single test file
composer analyse                 # PHPStan static analysis
composer format                  # Laravel Pint code formatting
```

## Architecture

This is a Laravel/Filament v5 package (`djl997/filament-model-activity-page`) that provides a reusable chat-style activity feed page for any Eloquent model. Namespace: `Djl997\FilamentModelActivityPage\`.

### Core flow

1. **`HasActivities` trait** ([src/Traits/HasActivities.php](src/Traits/HasActivities.php)) — added to any Eloquent model to give it a `activities()` morphMany relation, using the model class from config.

2. **`Activity` model** ([src/Models/Activity.php](src/Models/Activity.php)) — polymorphic model with fillable fields `user_id`, `message`, `level`, `is_internal`, `activitable_id`, `activitable_type`, `created_at`. Has a **global scope** that filters internal messages based on config callbacks (`internal_scope_callback` / `can_see_internal_callback`). No `updated_at` timestamp.

3. **`ActivityLevel` enum** ([src/Enums/ActivityLevel.php](src/Enums/ActivityLevel.php)) — `Chat` (user messages rendered as chat bubbles) vs `Event` (system events rendered as centered info pills). Icons only appear on `Event` activities.

4. **`ActivityMessage` DTO** ([src/Data/ActivityMessage.php](src/Data/ActivityMessage.php)) — readonly class used to format an `Activity` model for the view. Has calculated properties `by`, `by_user`, `date`.

5. **`ActivityPage` abstract base** ([src/Pages/ActivityPage.php](src/Pages/ActivityPage.php)) — the main extension point. Consuming apps subclass this, set `$resource`, and override hooks. Handles form submission, 60-second rate limiting for non-privileged users, message grouping (by date and consecutive author), and Livewire polling every 10 seconds.

### Override hooks on `ActivityPage`

| Method | Default | Purpose |
|---|---|---|
| `getEagerLoadRelations()` | `['activities.user']` | Add extra eager-load chains |
| `getChatMessages()` | loads `activities` on record | Override to aggregate from multiple related models |
| `isPrivilegedUser()` | `false` | Bypasses the 60-second rate limit |
| `canSendInternalMessages()` | `false` | Shows the internal checkbox on the compose form |
| `afterClientMessage(array $data)` | no-op | Send notifications after a non-privileged user posts |
| `resolveActivityIcon(array $activity)` | `null` | Return a Heroicon name to display on an Event activity |

### Configuration ([config/filament-model-activity-page.php](config/filament-model-activity-page.php))

- `activity_model` — swap in a custom Activity model
- `internal_scope_callback` — Eloquent query callback to filter internal messages at the DB level
- `can_see_internal_callback` — alternative: a closure returning bool for per-request visibility

### View ([resources/views/activity-page.blade.php](resources/views/activity-page.blade.php))

Tailwind-styled template. Consuming apps must add the package views path to their Filament theme CSS to avoid purged classes:

```css
@source '../../../../vendor/djl997/filament-model-activity-page/resources/views/**/*';
```

### Tests

Test fixtures live in `tests/` with a `TestCase` using Orchestra Testbench. Fixtures include a `Post` model, `User` model, `PostNotesPage`, and `PostResource`. Unit tests cover `ActivityMessage`, `HasActivities`, and `ActivityLevel`; feature tests cover `ActivityPage` interactions.
