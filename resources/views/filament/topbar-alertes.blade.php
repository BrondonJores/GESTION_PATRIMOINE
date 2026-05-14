@php
    use App\Filament\Resources\Alertes\AlerteResource;
    use App\Services\AlerteCounterService;
    use App\Support\Alertes\StockAlertType;
    use Filament\Support\Icons\Heroicon;

    $user = auth()->user();

    $openAlertesCount = $user
        ? app(AlerteCounterService::class)->openFor($user)
        : 0;

    $latestAlertes = $user
        ? app(AlerteCounterService::class)->latestOpenFor($user)
        : collect();

    $badge = $openAlertesCount > 99 ? '99+' : (string) $openAlertesCount;
@endphp

@if ($user?->can('view alertes'))
    <div
        x-data="{ open: false }"
        x-on:mouseenter="open = true"
        x-on:mouseleave="open = false"
        class="relative me-2"
    >
        <x-filament::icon-button
            :badge="$openAlertesCount > 0 ? $badge : null"
            badge-color="danger"
            color="gray"
            :href="AlerteResource::getUrl('index')"
            :icon="Heroicon::OutlinedExclamationTriangle"
            icon-size="lg"
            :label="$openAlertesCount > 0 ? __('Alertes : :count à traiter', ['count' => $openAlertesCount]) : __('Alertes')"
            tag="a"
            class="fi-topbar-alertes-btn"
        />

        <div
            x-cloak
            x-show="open"
            x-transition.opacity.duration.150ms
            class="absolute start-0 top-full z-50 mt-2 w-80 rounded-lg border border-gray-200 bg-white p-3 text-sm shadow-lg ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/10"
        >
            <div class="mb-3 flex items-center justify-between gap-3">
                <p class="font-semibold text-gray-950 dark:text-white">Alertes</p>
                <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $openAlertesCount }} à traiter
                </span>
            </div>

            <div class="space-y-2">
                @forelse ($latestAlertes as $alerte)
                    <a
                        href="{{ AlerteResource::getUrl('view', ['record' => $alerte]) }}"
                        class="block rounded-md border border-gray-200 px-3 py-2 transition hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="line-clamp-2 text-gray-800 dark:text-gray-100">
                                {{ $alerte->article?->designation ?? 'Article non renseigné' }}
                            </p>

                            <span @class([
                                'shrink-0 rounded-full px-2 py-0.5 text-xs font-medium',
                                'bg-warning-100 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' => $alerte->statut === 'En_cours',
                                'bg-danger-100 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' => $alerte->statut !== 'En_cours',
                            ])>
                                {{ $alerte->statut === 'En_cours' ? 'En cours' : 'Non traité' }}
                            </span>
                        </div>

                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ StockAlertType::label($alerte->type_alerte) }} · {{ $alerte->date_alerte?->diffForHumans() }}
                        </p>
                    </a>
                @empty
                    <p class="rounded-md border border-dashed border-gray-200 px-3 py-4 text-center text-gray-500 dark:border-white/10 dark:text-gray-400">
                        Aucune alerte à traiter.
                    </p>
                @endforelse
            </div>

            <a
                href="{{ AlerteResource::getUrl('index') }}"
                class="mt-3 block rounded-md px-3 py-2 text-center text-sm font-medium text-primary-600 hover:bg-primary-50 dark:text-primary-400 dark:hover:bg-primary-400/10"
            >
                Voir toutes les alertes
            </a>
        </div>
    </div>
@endif
