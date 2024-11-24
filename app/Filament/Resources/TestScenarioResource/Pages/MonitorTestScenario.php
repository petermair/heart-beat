<?php

namespace App\Filament\Resources\TestScenarioResource\Pages;

use App\Filament\Resources\TestScenarioResource;
use App\Models\TestResult;
use App\Models\TestScenario;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class MonitorTestScenario extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = TestScenarioResource::class;

    protected static string $view = 'filament.resources.test-scenario-resource.pages.monitor-test-scenario';

    public TestScenario $record;

    public function getTableQuery(): Builder
    {
        return TestResult::query()
            ->where('test_scenario_id', $this->record->id)
            ->latest();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ThingsBoard' => 'info',
                        'ChirpStack' => 'warning',
                        'MQTT' => 'success',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('flow_type')
                    ->badge(),
                Tables\Columns\IconColumn::make('success')
                    ->boolean(),
                Tables\Columns\TextColumn::make('error_message')
                    ->wrap()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('duration_ms')
                    ->label('Duration (ms)')
                    ->numeric(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->poll('10s');
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.test-scenario-resource.pages.monitor-header', [
            'scenario' => $this->record
        ]);
    }

    public function getSubheading(): string
    {
        return "MQTT Device: {$this->record->mqttDevice->name} | HTTP Device: {$this->record->httpDevice->name}";
    }
}
