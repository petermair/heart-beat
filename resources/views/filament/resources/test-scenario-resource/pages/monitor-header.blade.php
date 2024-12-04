@php
    $title = "Monitoring {$scenario->name}";
    $description = "MQTT Device: {$scenario->mqttDevice->name} | HTTP Device: " . ($scenario->httpDevice?->name ?? 'None');
@endphp

<div>
    <h2 class="text-2xl font-bold tracking-tight">
        {{ $title }}
    </h2>

    <p class="mt-2 text-sm text-gray-500">
        {{ $description }}
    </p>
</div>
