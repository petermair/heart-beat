<x-filament-panels::page>
    <x-filament::grid class="gap-4 lg:gap-8 mb-8">
        <x-filament::grid.column>
            <x-filament::section>
                <div class="flex flex-col gap-4">
                    <div class="grid grid-cols-3 gap-4">
                        {{-- ThingsBoard Status --}}
                        <div class="flex flex-col gap-2">
                            <div class="text-sm font-medium text-gray-500">ThingsBoard Status</div>
                            <div @class([
                                'px-4 py-3 rounded-lg text-sm font-medium',
                                'bg-success-50 text-success-700' => $this->record->thingsboard_status === 'HEALTHY',
                                'bg-warning-50 text-warning-700' => $this->record->thingsboard_status === 'WARNING',
                                'bg-danger-50 text-danger-700' => $this->record->thingsboard_status === 'CRITICAL',
                            ])>
                                {{ $this->record->thingsboard_status }}
                            </div>
                            <div class="text-sm">
                                Success Rate (1h): {{ number_format($this->record->thingsboard_success_rate_1h, 1) }}%
                            </div>
                        </div>

                        {{-- ChirpStack Status --}}
                        <div class="flex flex-col gap-2">
                            <div class="text-sm font-medium text-gray-500">ChirpStack Status</div>
                            <div @class([
                                'px-4 py-3 rounded-lg text-sm font-medium',
                                'bg-success-50 text-success-700' => $this->record->chirpstack_status === 'HEALTHY',
                                'bg-warning-50 text-warning-700' => $this->record->chirpstack_status === 'WARNING',
                                'bg-danger-50 text-danger-700' => $this->record->chirpstack_status === 'CRITICAL',
                            ])>
                                {{ $this->record->chirpstack_status }}
                            </div>
                            <div class="text-sm">
                                Success Rate (1h): {{ number_format($this->record->chirpstack_success_rate_1h, 1) }}%
                            </div>
                        </div>

                        {{-- MQTT Status --}}
                        <div class="flex flex-col gap-2">
                            <div class="text-sm font-medium text-gray-500">MQTT Status</div>
                            <div @class([
                                'px-4 py-3 rounded-lg text-sm font-medium',
                                'bg-success-50 text-success-700' => $this->record->mqtt_status === 'HEALTHY',
                                'bg-warning-50 text-warning-700' => $this->record->mqtt_status === 'WARNING',
                                'bg-danger-50 text-danger-700' => $this->record->mqtt_status === 'CRITICAL',
                            ])>
                                {{ $this->record->mqtt_status }}
                            </div>
                            <div class="text-sm">
                                Success Rate (1h): {{ number_format($this->record->mqtt_success_rate_1h, 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        </x-filament::grid.column>
    </x-filament::grid>

    {{ $this->table }}
</x-filament-panels::page>
