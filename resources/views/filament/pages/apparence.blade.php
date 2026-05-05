<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Personnalisation du panel</x-slot>

            <x-slot name="description">Couleurs, mode sombre et navigation latérale.</x-slot>

            {{ $this->form }}
        </x-filament::section>

        <x-filament::button type="submit">
            Enregistrer
        </x-filament::button>
    </form>
</x-filament-panels::page>
