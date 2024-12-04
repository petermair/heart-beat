<?php

namespace App\Filament\Resources;

use App\Enums\ServiceType;
use App\Enums\StatusType;
use App\Filament\Resources\TestScenarioResource\Pages;
use App\Models\TestScenario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Artisan;

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
                            ->label('HTTP Device')
                            ->nullable(),
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Forms\Components\TextInput::make('interval_seconds')
                            ->required()
                            ->numeric()
                            ->default(300)
                            ->minValue(30)
                            ->step(30),
                    ])->columns(2),

                Forms\Components\Section::make('Test Schedule')
                    ->schema([
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
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                // ThingsBoard Status
                Tables\Columns\TextColumn::make('thingsboard_status')
                    ->label('ThingsBoard')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
                // ChirpStack Status
                Tables\Columns\TextColumn::make('chirpstack_status')
                    ->label('ChirpStack')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
                // MQTT Status
                Tables\Columns\TextColumn::make('mqtt_tb_status')
                    ->label('MQTT TB')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
                Tables\Columns\TextColumn::make('mqtt_cs_status')
                    ->label('MQTT CS')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
                // LoRa Status
                Tables\Columns\TextColumn::make('lorarx_status')
                    ->label('LoRa RX')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
                Tables\Columns\TextColumn::make('loratx_status')
                    ->label('LoRa TX')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusType::HEALTHY->value => 'success',
                        StatusType::WARNING->value => 'warning',
                        StatusType::CRITICAL->value => 'danger',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst(strtolower($state))),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->trueLabel('Active Only')
                    ->falseLabel('Inactive Only'),
                // Service Status Filters
                Tables\Filters\SelectFilter::make('thingsboard_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('thingsboard_status', $status)
                        );
                    }),
                Tables\Filters\SelectFilter::make('chirpstack_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('chirpstack_status', $status)
                        );
                    }),
                Tables\Filters\SelectFilter::make('mqtt_tb_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('mqtt_tb_status', $status)
                        );
                    }),
                Tables\Filters\SelectFilter::make('mqtt_cs_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('mqtt_cs_status', $status)
                        );
                    }),
                Tables\Filters\SelectFilter::make('lorarx_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('lorarx_status', $status)
                        );
                    }),
                Tables\Filters\SelectFilter::make('loratx_status')
                    ->options([
                        StatusType::HEALTHY->value => StatusType::HEALTHY->label(),
                        StatusType::WARNING->value => StatusType::WARNING->label(),
                        StatusType::CRITICAL->value => StatusType::CRITICAL->label(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $status): Builder => $query->where('loratx_status', $status)
                        );
                    }),
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
                        Artisan::call('test-scenarios:run', [
                            '--scenario-id' => $record->id
                        ]);

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

    protected function getStatusDescription(TestScenario $scenario): string
    {
        $services = [
            ServiceType::THINGSBOARD->label() => $scenario->thingsboard_status,
            ServiceType::MQTT_TB->label() => $scenario->mqtt_tb_status,
            ServiceType::LORATX->label() => $scenario->loratx_status,
            ServiceType::MQTT_CS->label() => $scenario->mqtt_cs_status,
            ServiceType::CHIRPSTACK->label() => $scenario->chirpstack_status,
            ServiceType::LORARX->label() => $scenario->lorarx_status,
        ];
 
        return StatusHelper::getStatusDescription($services);
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
