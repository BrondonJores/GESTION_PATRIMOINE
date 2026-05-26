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
    <x-filament::dropdown
        placement="bottom-start"
        width="md"
        max-height="24rem"
        class="me-3"
    >
        <x-slot name="trigger">
            <x-filament::icon-button
                :badge="$openAlertesCount > 0 ? $badge : null"
                badge-color="danger"
                color="gray"
                :icon="Heroicon::OutlinedExclamationTriangle"
                icon-size="lg"
                :label="$openAlertesCount > 0 ? __('Alertes : :count à traiter', ['count' => $openAlertesCount]) : __('Alertes')"
                class="fi-topbar-alertes-btn"
            />
        </x-slot>

        <x-filament::dropdown.header :icon="Heroicon::OutlinedExclamationTriangle">
            Alertes

            @if ($openAlertesCount > 0)
                ({{ $badge }})
            @endif
        </x-filament::dropdown.header>

        <x-filament::dropdown.list>
            @forelse ($latestAlertes as $alerte)
                <x-filament::dropdown.list.item
                    :badge="$alerte->statut === 'En_cours' ? 'En cours' : 'Non traité'"
                    :badge-color="$alerte->statut === 'En_cours' ? 'warning' : 'danger'"
                    :href="AlerteResource::getUrl('view', ['record' => $alerte])"
                    :icon="$alerte->statut === 'En_cours' ? Heroicon::OutlinedClock : Heroicon::OutlinedExclamationTriangle"
                    :icon-color="$alerte->statut === 'En_cours' ? 'warning' : 'danger'"
                    tag="a"
                >
                    {{ str(($alerte->article?->designation ?? 'Article non renseigné') . ' - ' . StockAlertType::label($alerte->type_alerte))->limit(76) }}
                </x-filament::dropdown.list.item>
            @empty
                <x-filament::dropdown.list.item disabled>
                    Aucune alerte à traiter.
                </x-filament::dropdown.list.item>
            @endforelse

            <x-filament::dropdown.list.item
                :href="AlerteResource::getUrl('index')"
                :icon="Heroicon::OutlinedQueueList"
                color="primary"
                tag="a"
            >
                Voir toutes les alertes
            </x-filament::dropdown.list.item>
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif
