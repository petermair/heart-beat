<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Jobs\DeviceMonitoringJob;
use App\Models\Device;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 2;

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
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Server Configuration')
                    ->schema([
                        Forms\Components\Select::make('thingsboard_server_id')
                            ->label('ThingsBoard Server')
                            ->options(fn () => Server::whereHas('serverType', fn ($query) => 
                                $query->where('name', 'thingsboard')
                            )->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('chirpstack_server_id')
                            ->label('ChirpStack Server')
                            ->options(fn () => Server::whereHas('serverType', fn ($query) => 
                                $query->where('name', 'chirpstack')
                            )->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('communication_type_id')
                            ->relationship('communicationType', 'label')
                            ->required()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('ChirpStack Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('application_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('device_profile_id')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('device_eui')
                            ->required()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Monitoring Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('monitoring_enabled')
                            ->label('Enable Monitoring')
                            ->default(true),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('device_eui')
                    ->label('Device EUI')
                    ->searchable(),
                Tables\Columns\TextColumn::make('thingsboardServer.name')
                    ->label('ThingsBoard Server')
                    ->sortable(),
                Tables\Columns\TextColumn::make('chirpstackServer.name')
                    ->label('ChirpStack Server')
                    ->sortable(),
                Tables\Columns\TextColumn::make('communicationType.label')
                    ->label('Communication')
                    ->sortable(),
                Tables\Columns\TextColumn::make('success_rate')
                    ->label('Success Rate')
                    ->getStateUsing(fn (Device $record): string => $record->getSuccessRate() . '%')
                    ->color(fn (Device $record): string => 
                        match(true) {
                            $record->getSuccessRate() >= 90 => 'success',
                            $record->getSuccessRate() >= 70 => 'warning',
                            default => 'danger',
                        }
                    ),
                Tables\Columns\TextColumn::make('avg_response_time')
                    ->label('Avg Response (ms)')
                    ->getStateUsing(fn (Device $record): string => 
                        $record->getAverageResponseTime() ? 
                        number_format($record->getAverageResponseTime(), 2) : 
                        'N/A'
                    ),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('monitoring_enabled')
                    ->label('Monitoring')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_seen_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('thingsboard_server')
                    ->relationship('thingsboardServer', 'name'),
                Tables\Filters\SelectFilter::make('chirpstack_server')
                    ->relationship('chirpstackServer', 'name'),
                Tables\Filters\SelectFilter::make('communication_type')
                    ->relationship('communicationType', 'label'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('monitoring_enabled')
                    ->label('Monitoring'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('test')
                        ->label('Test Selected')
                        ->icon('heroicon-o-play')
                        ->color('success')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                DeviceMonitoringJob::dispatch($record);
                            }
                            
                            Notification::make()
                                ->title('Tests Started')
                                ->body('Device monitoring tests have been initiated.')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\MonitoringResultsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['thingsboardServer', 'chirpstackServer', 'communicationType']);
    }
}
