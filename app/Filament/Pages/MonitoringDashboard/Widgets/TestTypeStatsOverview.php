<?php

namespace App\Filament\Pages\MonitoringDashboard\Widgets;

use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TestTypeStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $stats = [];
        $testTypes = ['mqtt_rx', 'mqtt_tx', 'http_health', 'telemetry', 'rpc'];
        
        foreach ($testTypes as $type) {
            $results = TestResult::where('test_type', $type)
                ->where('created_at', '>=', now()->subDay())
                ->selectRaw('(COUNT(CASE WHEN success = 1 THEN 1 END) * 100.0 / COUNT(*)) as success_rate')
                ->first();

            $successRate = $results?->success_rate ?? 0;
            
            $stats[] = Stat::make(
                ucwords(str_replace('_', ' ', $type)),
                number_format($successRate, 1) . '%'
            )
                ->icon('heroicon-o-check-circle')
                ->color($successRate >= 90 ? 'success' : ($successRate >= 75 ? 'warning' : 'danger'));
        }

        return $stats;
    }
}
