<?php

namespace App\Filament\Widgets;

use App\Models\TestResult;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ResponseTimeChart extends ChartWidget
{
    protected static ?string $heading = 'Response Times by Flow Type';
    protected static ?string $maxHeight = '300px';
    protected static ?string $pollingInterval = '15s';

    protected function getData(): array
    {
        $flowStats = TestResult::where('created_at', '>=', now()->subDay())
            ->select(
                'flow_type',
                DB::raw('AVG(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as avg_response'),
                DB::raw('MIN(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as min_response'),
                DB::raw('MAX(CASE WHEN status = "SUCCESS" THEN execution_time_ms ELSE NULL END) as max_response')
            )
            ->groupBy('flow_type')
            ->orderByRaw('CASE 
                WHEN flow_type = "FULL_ROUTE_1" THEN 1
                WHEN flow_type = "ONE_WAY_ROUTE" THEN 2
                WHEN flow_type = "TWO_WAY_ROUTE" THEN 3
                WHEN flow_type = "DIRECT_TEST_1" THEN 4
                WHEN flow_type = "DIRECT_TEST_2" THEN 5
                WHEN flow_type = "TB_MQTT_HEALTH" THEN 6
                WHEN flow_type = "CS_MQTT_HEALTH" THEN 7
                WHEN flow_type = "TB_HTTP_HEALTH" THEN 8
                WHEN flow_type = "CS_HTTP_HEALTH" THEN 9
                ELSE 10 END')
            ->get();

        $labels = [];
        $avgData = [];
        $minData = [];
        $maxData = [];

        foreach ($flowStats as $stat) {
            $flowType = $stat->flow_type;
            $protocol = str_contains($flowType, 'HTTP') ? 'HTTP' : 'MQTT';
            
            $labels[] = str_replace('_', ' ', $flowType) . "\n({$protocol})";
            $avgData[] = round($stat->avg_response ?? 0);
            $minData[] = round($stat->min_response ?? 0);
            $maxData[] = round($stat->max_response ?? 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average Response Time (ms)',
                    'data' => $avgData,
                    'borderColor' => '#60A5FA',
                    'backgroundColor' => '#60A5FA',
                ],
                [
                    'label' => 'Min Response Time (ms)',
                    'data' => $minData,
                    'borderColor' => '#34D399',
                    'backgroundColor' => '#34D399',
                ],
                [
                    'label' => 'Max Response Time (ms)',
                    'data' => $maxData,
                    'borderColor' => '#F87171',
                    'backgroundColor' => '#F87171',
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Response Time (ms)',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Flow Type',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }
}
