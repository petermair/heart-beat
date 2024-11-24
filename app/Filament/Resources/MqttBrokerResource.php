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
                        Forms\Components\Toggle::make('ssl_enabled')
                            ->required()
                            ->default(false),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Monitoring Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true),
                        Forms\Components\TextInput::make('monitoring_interval')
                            ->required()
                            ->numeric()
                            ->default(300)
                            ->suffix('seconds')
                            ->minValue(60)
                            ->maxValue(86400),
                        Forms\Components\TextInput::make('test_topic')
                            ->maxLength(255)
                            ->placeholder('test/heartbeat'),
                    ])->columns(2),

                Forms\Components\Section::make('Authentication')
                    ->schema([
                        Forms\Components\KeyValue::make('credentials')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addable()
                            ->deletable()
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\IconColumn::make('ssl_enabled')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('monitoring_interval')
                    ->suffix(' seconds'),
                Tables\Columns\TextColumn::make('test_topic')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
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
