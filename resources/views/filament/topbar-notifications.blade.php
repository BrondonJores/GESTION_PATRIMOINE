@php
    use App\Filament\Resources\Notifications\NotificationResource;
    use App\Services\NotificationCounterService;
    use Filament\Support\Icons\Heroicon;

    $user = auth()->user();

    $unreadNotificationsCount = $user
        ? app(NotificationCounterService::class)->unreadFor($user)
        : 0;

    $badge = $unreadNotificationsCount > 99 ? '99+' : (string) $unreadNotificationsCount;
@endphp

@if ($user?->can('view notifications'))
    <x-filament::icon-button
        :badge="$unreadNotificationsCount > 0 ? $badge : null"
        badge-color="danger"
        color="gray"
        :href="NotificationResource::getUrl('index')"
        :icon="Heroicon::OutlinedBell"
        icon-size="lg"
        :label="$unreadNotificationsCount > 0 ? __('Notifications : :count non lue(s)', ['count' => $unreadNotificationsCount]) : __('Notifications')"
        tag="a"
        class="fi-topbar-notifications-btn"
    />
@endif
