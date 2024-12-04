<?php

namespace App\Filament\Widgets;

use App\Models\TestResult;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class FlowStatsOverview extends BaseWidget
{
    protected static ?string $pollingInterval = '15s';

    protected function getStats(): array
    {
        // Calculate Message Flow Stats (All test results)
        $messageFlowStats = TestResult::where('created_at', '>=', now()->subDay())
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "SUCCESS" THEN 1 ELSE 0 END) as successes,
                AVG(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as avg_response
            ')
            ->first();

        // Calculate success rates
        $messageFlowSuccessRate = $messageFlowStats->total > 0 ? ($messageFlowStats->successes * 100 / $messageFlowStats->total) : 0;

        return [
            Stat::make('Test Scenario Success Rate (24h)', number_format($messageFlowSuccessRate, 1).'%')
                ->description('All Test Scenarios')
                ->icon('heroicon-o-signal')
                ->color($messageFlowSuccessRate >= 90 ? 'success' : ($messageFlowSuccessRate >= 75 ? 'warning' : 'danger')),

            Stat::make('Average Response Time', number_format($messageFlowStats->avg_response ?? 0, 0).'ms')
                ->description('All test executions')
                ->icon('heroicon-o-clock')
                ->color('info'),

            Stat::make('Total Tests Run (24h)', number_format($messageFlowStats->total))
                ->description('Last 24 hours')
                ->icon('heroicon-o-beaker')
                ->color('success'),
        ];
    }
}
