<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestScenarioResource\Pages;
use App\Models\TestScenario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use App\Jobs\ExecuteTestScenarioJob;

class TestScenarioResource extends Resource
{
    protected static ?string $model = TestScenario::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    
    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'success' : 'gray';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535),
                        Forms\Components\Select::make('mqtt_device_id')
                            ->relationship('mqttDevice', 'name')
                            ->required()
                            ->label('MQTT Device'),
                        Forms\Components\Select::make('http_device_id')
                            ->relationship('httpDevice', 'name')
                            ->required()
                            ->label('HTTP Device'),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Test Schedule')
                    ->schema([
                        Forms\Components\TextInput::make('interval_seconds')
                            ->label('Test Interval (seconds)')
                            ->numeric()
                            ->default(300)
                            ->required()
                            ->helperText('How often to run the tests'),
                        Forms\Components\TextInput::make('timeout_seconds')
                            ->label('Timeout (seconds)')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->helperText('Maximum time to wait for response'),
                        Forms\Components\TextInput::make('max_retries')
                            ->label('Max Retries')
                            ->numeric()
                            ->default(3)
                            ->required()
                            ->helperText('Number of retry attempts'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mqttDevice.name')
                    ->label('MQTT Device')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('httpDevice.name')
                    ->label('HTTP Device')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                // ThingsBoard Status
                Tables\Columns\TextColumn::make('thingsboard_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'HEALTHY' => 'success',
                        'WARNING' => 'warning',
                        'CRITICAL' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('thingsboard_success_rate_1h')
                    ->label('TB 1h')
                    ->numeric(2)
                    ->suffix('%')
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger'
                    }),
                // ChirpStack Status
                Tables\Columns\TextColumn::make('chirpstack_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'HEALTHY' => 'success',
                        'WARNING' => 'warning',
                        'CRITICAL' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('chirpstack_success_rate_1h')
                    ->label('CS 1h')
                    ->numeric(2)
                    ->suffix('%')
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger'
                    }),
                // MQTT Status
                Tables\Columns\TextColumn::make('mqtt_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'HEALTHY' => 'success',
                        'WARNING' => 'warning',
                        'CRITICAL' => 'danger',
                        default => 'gray'
                    }),
                Tables\Columns\TextColumn::make('mqtt_success_rate_1h')
                    ->label('MQTT 1h')
                    ->numeric(2)
                    ->suffix('%')
                    ->color(fn ($state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger'
                    }),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
                Tables\Filters\SelectFilter::make('mqtt_device')
                    ->relationship('mqttDevice', 'name'),
                Tables\Filters\SelectFilter::make('http_device')
                    ->relationship('httpDevice', 'name'),
                // Service Status Filters
                Tables\Filters\SelectFilter::make('thingsboard_status')
                    ->options([
                        'HEALTHY' => 'Healthy',
                        'WARNING' => 'Warning',
                        'CRITICAL' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('chirpstack_status')
                    ->options([
                        'HEALTHY' => 'Healthy',
                        'WARNING' => 'Warning',
                        'CRITICAL' => 'Critical',
                    ]),
                Tables\Filters\SelectFilter::make('mqtt_status')
                    ->options([
                        'HEALTHY' => 'Healthy',
                        'WARNING' => 'Warning',
                        'CRITICAL' => 'Critical',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('monitor')
                    ->icon('heroicon-m-chart-bar')
                    ->color('info')
                    ->url(fn (TestScenario $record): string => static::getUrl('monitor', ['record' => $record])),
                Tables\Actions\Action::make('run')
                    ->icon('heroicon-m-play')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (TestScenario $record) {
                        ExecuteTestScenarioJob::dispatch($record);
                        
                        Notification::make()
                            ->title('Test Execution Started')
                            ->success()
                            ->body("Test scenario '{$record->name}' has been queued for execution.")
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->icon('heroicon-m-check-circle')
                        ->color('success')
                        ->action(fn (Builder $query) => $query->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->icon('heroicon-m-x-circle')
                        ->color('danger')
                        ->action(fn (Builder $query) => $query->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTestScenarios::route('/'),
            'create' => Pages\CreateTestScenario::route('/create'),
            'edit' => Pages\EditTestScenario::route('/{record}/edit'),
            'monitor' => Pages\MonitorTestScenario::route('/{record}/monitor'),
        ];
    }
}
