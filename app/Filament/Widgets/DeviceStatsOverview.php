<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviceStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $totalTests = TestResult::where('created_at', '>=', now()->subDay())->count();

        // Count devices with MQTT flow failures
        $mqttFlows = [
            'FULL_ROUTE_1',
            'ONE_WAY_ROUTE',
            'TWO_WAY_ROUTE',
            'DIRECT_TEST_1',
            'DIRECT_TEST_2',
            'TB_MQTT_HEALTH',
            'CS_MQTT_HEALTH',
        ];

        $failedDevices = Device::whereHas('testResults', function ($query) use ($mqttFlows) {
            $query->where('created_at', '>=', now()->subDay())
                ->where('status', 'FAILURE')
                ->whereIn('flow_type', $mqttFlows);
        })->count();

        $avgResponseTime = TestResult::where('created_at', '>=', now()->subDay())
            ->where('status', 'SUCCESS')
            ->whereNotNull('execution_time_ms')
            ->avg('execution_time_ms');

        return [
            Stat::make('Total Tests (24h)', (string) $totalTests)
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
            Stat::make('Devices with MQTT Failures', (string) $failedDevices)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($failedDevices === 0 ? 'success' : 'danger'),
            Stat::make('Avg Response Time', number_format($avgResponseTime ?? 0, 2).'ms')
                ->icon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
