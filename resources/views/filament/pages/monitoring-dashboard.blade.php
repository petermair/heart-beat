<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 mb-6">
        @foreach ($this->getWidgets() as $key => $widget)
            <div @class([
                'lg:col-span-3' => $key === 'response-time',
                'lg:col-span-1' => $key !== 'response-time',
            ])>
                @livewire($widget)
            </div>
        @endforeach
    </div>

    <x-filament::section>
        <x-slot name="heading">Recent Test Failures</x-slot>
        <x-slot name="description">Test failures in the last 24 hours, showing flow execution details and error messages</x-slot>

        {{ $this->table }}
    </x-filament::section>
</x-filament-panels::page>
