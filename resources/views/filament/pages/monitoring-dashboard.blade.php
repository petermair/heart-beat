<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 mb-6">
        @foreach ($this->getWidgets() as $key => $widget)
            <div @class([
                'lg:col-span-2' => $key === 'response-time',
            ])>
                @livewire($widget)
            </div>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">Recent Failures (Last 24 Hours)</x-slot>
        <x-slot name="description">List of test failures in the last 24 hours</x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
