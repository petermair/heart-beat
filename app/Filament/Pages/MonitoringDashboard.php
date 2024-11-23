<?php

namespace App\Filament\Pages;

use App\Models\Device;
use App\Models\TestScenario;
use App\Models\TestResult;
use Filament\Pages\Page;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\Grid;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Carbon\Carbon;

class MonitoringDashboard extends Page implements HasTable
{
    use InteractsWithTable;
    
    protected static string $view = 'filament.pages.monitoring-dashboard';
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Monitoring Dashboard';
    protected static ?string $title = 'Monitoring Dashboard';
    protected static ?int $navigationSort = 1;
    protected ?string $heading = 'System Monitoring Dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            MonitoringDashboard\Widgets\SystemStatsOverview::class,
            MonitoringDashboard\Widgets\TestTypeStatsOverview::class,
            MonitoringDashboard\Widgets\DeviceStatsOverview::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                TestResult::query()
                    ->where('success', false)
                    ->where('created_at', '>=', now()->subDay())
                    ->latest()
            )
            ->columns([
                TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('test_type')
                    ->label('Test Type')
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state)))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('error_message')
                    ->label('Error Message')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->heading('Recent Failures (Last 24 Hours)')
            ->description('List of test failures in the last 24 hours');
    }
}
