<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\TestResult;
use App\Models\TestScenario;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SystemStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $activeDevices = Device::where('is_active', true)->count();
        $monitoredDevices = Device::where('monitoring_enabled', true)->count();
        $activeScenarios = TestScenario::where('is_active', true)->count();

        return [
            Stat::make('Active Devices', (string)$activeDevices)
                ->description('Devices marked as active')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('success'),

            Stat::make('Monitored Devices', (string)$monitoredDevices)
                ->description('Devices with monitoring enabled')
                ->icon('heroicon-o-signal')
                ->color('info'),

            Stat::make('Active Test Scenarios', (string)$activeScenarios)
                ->description('Test scenarios currently running')
                ->icon('heroicon-o-play')
                ->color('success'),
        ];
    }
}
