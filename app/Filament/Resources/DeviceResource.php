<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Models\Device;
use App\Models\Server;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
                            ->options(fn () => Server::whereHas('serverType', fn ($query) => $query->where('name', 'thingsboard')
                            )->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('chirpstack_server_id')
                            ->label('ChirpStack Server')
                            ->options(fn () => Server::whereHas('serverType', fn ($query) => $query->where('name', 'chirpstack')
                            )->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Forms\Components\Select::make('communication_type_id')
                            ->label('Communication Type')
                            ->options([
                                1 => 'MQTT',
                                2 => 'HTTP',
                            ])
                            ->required(),
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
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('thingsboardServer.name')
                    ->label('ThingsBoard Server')
                    ->searchable(),
                Tables\Columns\TextColumn::make('chirpstackServer.name')
                    ->label('ChirpStack Server')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
