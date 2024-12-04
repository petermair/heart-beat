<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MqttBrokerResource\Pages;
use App\Models\MqttBroker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MqttBrokerResource extends Resource
{
    protected static ?string $model = MqttBroker::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Broker Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('host')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('port')
                            ->required()
                            ->numeric()
                            ->default(1883)
                            ->minValue(1)
                            ->maxValue(65535),
                    ])->columns(2),

                Forms\Components\Section::make('Authentication')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null)
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('SSL Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('ssl_enabled')
                            ->required()
                            ->default(false)
                            ->reactive(),
                        Forms\Components\Textarea::make('ssl_ca')
                            ->label('SSL CA Certificate')
                            ->visible(fn (callable $get) => $get('ssl_enabled'))
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('ssl_cert')
                            ->label('SSL Client Certificate')
                            ->visible(fn (callable $get) => $get('ssl_enabled'))
                            ->maxLength(65535),
                        Forms\Components\Textarea::make('ssl_key')
                            ->label('SSL Client Key')
                            ->visible(fn (callable $get) => $get('ssl_enabled'))
                            ->maxLength(65535),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('host')
                    ->searchable(),
                Tables\Columns\TextColumn::make('port'),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\IconColumn::make('ssl_enabled')
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
            ->filters([
                Tables\Filters\TernaryFilter::make('ssl_enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMqttBrokers::route('/'),
            'create' => Pages\CreateMqttBroker::route('/create'),
            'edit' => Pages\EditMqttBroker::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'success' : 'gray';
    }
}
