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
    <div
        x-data="{ open: false }"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
        class="relative me-2"
    >
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

        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            class="absolute end-0 top-full z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white p-3 text-sm shadow-lg ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="font-semibold text-gray-950 dark:text-white">Notifications</p>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $unreadNotificationsCount }} non lue(s)
                </span>
            </div>

            <div class="space-y-2">
                @forelse ($latestNotifications as $notification)
                    <a
                        href="{{ NotificationResource::getUrl('view', ['record' => $notification]) }}"
                        class="block rounded-md border border-gray-200 px-3 py-2 transition hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="line-clamp-2 text-gray-800 dark:text-gray-100">
                                {{ $notification->contenu }}
                            </p>

                            @unless ($notification->lu)
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-danger-500"></span>
                            @endunless
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $notification->date_envoi?->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <p class="rounded-md border border-dashed border-gray-200 px-3 py-4 text-center text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Aucune notification récente.
                    </p>
                @endforelse
            </div>

            <a
                href="{{ NotificationResource::getUrl('index') }}"
                class="mt-3 block rounded-md px-3 py-2 text-center text-sm font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-400/10"
            >
                Voir toutes les notifications
            </a>
        </div>
    </div>
@endif
