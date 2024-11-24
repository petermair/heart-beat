<?php

namespace App\Filament\Widgets;

use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TestTypeStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        $stats = [];
        
        // Group flow types by their general category
        $typeMapping = [
            'MQTT RX' => [
                'FULL_ROUTE_1',
                'ONE_WAY_ROUTE',
                'TWO_WAY_ROUTE',
                'DIRECT_TEST_1',
                'DIRECT_TEST_2',
            ],
            'MQTT Health' => [
                'TB_MQTT_HEALTH',
                'CS_MQTT_HEALTH',
            ],
            'HTTP Health' => [
                'TB_HTTP_HEALTH',
                'CS_HTTP_HEALTH',
            ],
        ];

        foreach ($typeMapping as $displayName => $flowTypes) {
            $results = TestResult::whereIn('flow_type', $flowTypes)
                ->where('created_at', '>=', now()->subDay())
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = "SUCCESS" THEN 1 ELSE 0 END) as successes,
                    AVG(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as avg_response
                ')
                ->first();

            $total = $results->total ?? 0;
            $successRate = $total > 0 ? (($results->successes ?? 0) * 100.0 / $total) : 0;
            $avgResponse = $results->avg_response ?? 0;

            $stats[] = Stat::make($displayName, number_format($successRate, 1) . '%')
                ->description(sprintf(
                    '%d tests, avg %dms',
                    $total,
                    round($avgResponse)
                ))
                ->color($successRate >= 90 ? 'success' : ($successRate >= 75 ? 'warning' : 'danger'));
        }

        return $stats;
    }
}
