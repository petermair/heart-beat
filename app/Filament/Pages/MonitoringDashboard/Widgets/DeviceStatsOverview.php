<?php

namespace App\Filament\Pages\MonitoringDashboard\Widgets;

use App\Models\Device;
use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviceStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalTests = TestResult::where('created_at', '>=', now()->subDay())->count();
        $failedDevices = Device::whereHas('testResults', function ($query) {
            $query->where('created_at', '>=', now()->subDay())
                ->where('success', false);
        })->count();
        $avgResponseTime = TestResult::where('created_at', '>=', now()->subDay())
            ->whereNotNull('response_time')
            ->avg('response_time');

        return [
            Stat::make('Total Tests (24h)', (string)$totalTests)
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info'),
            Stat::make('Devices with Failures', (string)$failedDevices)
                ->icon('heroicon-o-exclamation-triangle')
                ->color($failedDevices === 0 ? 'success' : 'danger'),
            Stat::make('Avg Response Time', number_format($avgResponseTime ?? 0, 2) . 'ms')
                ->icon('heroicon-o-clock')
                ->color('info'),
        ];
    }
}
