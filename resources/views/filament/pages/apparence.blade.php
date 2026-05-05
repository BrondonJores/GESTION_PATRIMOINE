<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">
                Couleurs du panel
            </x-slot>

            <x-slot name="description">
                Personnalisez les couleurs principales de l’interface d’administration.
            </x-slot>

            {{ $this->form }}
        </x-filament::section>
        <br>
        <x-filament::button type="submit">
            Enregistrer
        </x-filament::button>
    </form>
</x-filament-panels::page>
