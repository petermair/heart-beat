<?php

namespace App\Filament\Widgets;

use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class FlowStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Calculate MQTT Flow Stats
        $mqttFlows = [
            'FULL_ROUTE_1',
            'ONE_WAY_ROUTE',
            'TWO_WAY_ROUTE',
            'DIRECT_TEST_1',
            'DIRECT_TEST_2',
            'TB_MQTT_HEALTH',
            'CS_MQTT_HEALTH'
        ];

        $mqttStats = TestResult::where('created_at', '>=', now()->subDay())
            ->whereIn('flow_type', $mqttFlows)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "SUCCESS" THEN 1 ELSE 0 END) as successes,
                AVG(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as avg_response
            ')
            ->first();

        // Calculate HTTP Flow Stats
        $httpFlows = ['TB_HTTP_HEALTH', 'CS_HTTP_HEALTH'];

        $httpStats = TestResult::where('created_at', '>=', now()->subDay())
            ->whereIn('flow_type', $httpFlows)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "SUCCESS" THEN 1 ELSE 0 END) as successes,
                AVG(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as avg_response
            ')
            ->first();

        // Calculate success rates
        $mqttSuccessRate = $mqttStats->total > 0 ? ($mqttStats->successes * 100 / $mqttStats->total) : 0;
        $httpSuccessRate = $httpStats->total > 0 ? ($httpStats->successes * 100 / $httpStats->total) : 0;

        return [
            Stat::make('MQTT Success Rate (24h)', number_format($mqttSuccessRate, 1) . '%')
                ->description('Flows 1-7')
                ->icon('heroicon-o-signal')
                ->color($mqttSuccessRate >= 90 ? 'success' : ($mqttSuccessRate >= 75 ? 'warning' : 'danger')),

            Stat::make('MQTT Avg Response', number_format($mqttStats->avg_response ?? 0, 0) . 'ms')
                ->description('Average response time')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('HTTP Success Rate (24h)', number_format($httpSuccessRate, 1) . '%')
                ->description('HTTP Health Checks')
                ->icon('heroicon-o-globe-alt')
                ->color($httpSuccessRate >= 90 ? 'success' : ($httpSuccessRate >= 75 ? 'warning' : 'danger')),
        ];
    }
}
