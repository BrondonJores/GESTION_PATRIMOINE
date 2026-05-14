@php
    use App\Filament\Resources\Notifications\NotificationResource;
    use App\Services\NotificationCounterService;
    use Filament\Support\Icons\Heroicon;

    $user = auth()->user();

    $unreadNotificationsCount = $user
        ? app(NotificationCounterService::class)->unreadFor($user)
        : 0;

    $latestNotifications = $user
        ? app(NotificationCounterService::class)->latestFor($user)
        : collect();

    $badge = $unreadNotificationsCount > 99 ? '99+' : (string) $unreadNotificationsCount;
@endphp

@if ($user?->can('view notifications'))
    <x-filament::dropdown
        placement="bottom-end"
        width="md"
        max-height="24rem"
        class="me-2"
    >
        <x-slot name="trigger">
            <x-filament::icon-button
                :badge="$unreadNotificationsCount > 0 ? $badge : null"
                badge-color="danger"
                color="gray"
                :icon="Heroicon::OutlinedBell"
                icon-size="lg"
                :label="$unreadNotificationsCount > 0 ? __('Notifications : :count non lue(s)', ['count' => $unreadNotificationsCount]) : __('Notifications')"
                class="fi-topbar-notifications-btn"
            />
        </x-slot>

        <x-filament::dropdown.header :icon="Heroicon::OutlinedBell">
            Notifications

            @if ($unreadNotificationsCount > 0)
                ({{ $badge }})
            @endif
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            @forelse ($latestNotifications as $notification)
                <x-filament::dropdown.list.item
                    :badge="$notification->lu ? null : 'Non lue'"
                    badge-color="danger"
                    :href="NotificationResource::getUrl('view', ['record' => $notification])"
                    :icon="$notification->lu ? Heroicon::OutlinedEnvelopeOpen : Heroicon::OutlinedEnvelope"
                    :icon-color="$notification->lu ? 'gray' : 'danger'"
                    tag="a"
                >
                    {{ str($notification->contenu)->limit(72) }}
                </x-filament::dropdown.list.item>
            @empty
                <x-filament::dropdown.list.item disabled>
                    Aucune notification récente.
                </x-filament::dropdown.list.item>
            @endforelse

            <x-filament::dropdown.list.item
                :href="NotificationResource::getUrl('index')"
                :icon="Heroicon::OutlinedQueueList"
                color="primary"
                tag="a"
            >
                Voir toutes les notifications
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif
