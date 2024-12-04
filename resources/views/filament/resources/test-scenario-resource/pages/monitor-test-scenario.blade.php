<x-filament-panels::page>
    {{-- Overview Section --}}
    {{ $this->infolist }}

    {{-- Status Section --}}
    <x-filament::grid class="gap-4 lg:gap-8 mb-8">
        <x-filament::grid.column>
            <x-filament::section>
                <x-slot name="heading">ThingsBoard Status</x-slot>
                <x-status-badge :status="$this->record->thingsboard_status" :color="$this->record->getStatusColor($this->record->thingsboard_status)" />
                <div class="mt-2">Success Rate (1h): {{ number_format($this->record->thingsboard_success_rate_1h, 1) }}%</div>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">ChirpStack Status</x-slot>
                <x-status-badge :status="$this->record->chirpstack_status" :color="$this->record->getStatusColor($this->record->chirpstack_status)" />
                <div class="mt-2">Success Rate (1h): {{ number_format($this->record->chirpstack_success_rate_1h, 1) }}%</div>
            </x-filament::section>
        </x-filament::grid.column>
    </x-filament::grid>
</x-filament-panels::page>
