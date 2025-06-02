<x-filament::page>
    {{$this->settingsForm}}

    <div class="text-right">
        <x-filament::button type="submit" wire:click="saveForm">
            Save
        </x-filament::button>
    </div>
</x-filament::page>
