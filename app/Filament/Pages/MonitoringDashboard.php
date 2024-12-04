<?php

namespace App\Filament\Pages;

use App\Models\TestResult;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class MonitoringDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.monitoring-dashboard';

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'Monitoring Dashboard';

    protected static ?string $title = 'Monitoring Dashboard';

    protected static ?int $navigationSort = 1;

    protected ?string $heading = 'System Monitoring Dashboard';

    protected function getWidgets(): array
    {
        return [
            'system-stats' => \App\Filament\Widgets\SystemStatsOverview::class,
            'flow-stats' => \App\Filament\Widgets\FlowStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TestResult::query()
                    ->where('status', 'FAILURE')
                    ->where('created_at', '>=', now()->subDay())
                    ->latest()
            )
            ->columns([
                TextColumn::make('test_scenario.name')
                    ->label('Test Scenario')
                    ->searchable(),
                TextColumn::make('test_scenario.mqttDevice.name')
                    ->label('MQTT Device')
                    ->searchable(),
                TextColumn::make('flow_number')
                    ->label('Flow #')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'SUCCESS' => 'success',
                        'FAILURE' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('execution_time_ms')
                    ->label('Response Time (ms)')
                    ->numeric(),
                TextColumn::make('error_message')
                    ->label('Error Message')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
