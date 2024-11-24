<?php

namespace App\Filament\Pages\MonitoringDashboard\Widgets;

use App\Models\Device;
use App\Models\TestScenario;
use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalDevices = Device::count();
        $activeScenarios = TestScenario::count();
        $lastHourResult = TestResult::where('created_at', '>=', now()->subHour())
            ->selectRaw('(COUNT(CASE WHEN success = 1 THEN 1 END) * 100.0 / COUNT(*)) as success_rate')
            ->first();
        $lastHourSuccess = $lastHourResult ? $lastHourResult->success_rate : 0;

        return [
            Stat::make('Total Devices', (string)$totalDevices)
                ->icon('heroicon-o-device-phone-mobile')
                ->color('info'),
            Stat::make('Active Test Scenarios', (string)$activeScenarios)
                ->icon('heroicon-o-play')
                ->color('success'),
            Stat::make('Last Hour Success Rate', number_format($lastHourSuccess, 1) . '%')
                ->icon('heroicon-o-chart-bar')
                ->color($lastHourSuccess >= 90 ? 'success' : ($lastHourSuccess >= 75 ? 'warning' : 'danger')),
        ];
    }
}
