<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationTypeResource\Pages;
use App\Models\NotificationType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationTypeResource extends Resource
{
    protected static ?string $model = NotificationType::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';
    
    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 10;

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
                        Forms\Components\TextInput::make('display_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Configuration Schema')
                    ->description('Define the JSON schema for configuration validation')
                    ->schema([
                        Forms\Components\JsonEditor::make('configuration_schema')
                            ->default([
                                'url' => [
                                    'type' => 'url',
                                    'label' => 'Webhook URL',
                                    'required' => true,
                                    'help' => 'The URL to send notifications to'
                                ],
                                'method' => [
                                    'type' => 'select',
                                    'label' => 'HTTP Method',
                                    'required' => true,
                                    'options' => [
                                        'POST' => 'POST',
                                        'GET' => 'GET',
                                        'PUT' => 'PUT'
                                    ],
                                    'default' => 'POST'
                                ],
                                'headers' => [
                                    'type' => 'text',
                                    'label' => 'HTTP Headers',
                                    'required' => false,
                                    'help' => 'Additional headers in JSON format'
                                ]
                            ])
                            ->helperText('Each field should have: type (text, number, url, email, select), label, required (true/false), and optional help text')
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
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('notification_settings_count')
                    ->counts('notificationSettings')
                    ->label('Settings'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListNotificationTypes::route('/'),
            'create' => Pages\CreateNotificationType::route('/create'),
            'edit' => Pages\EditNotificationType::route('/{record}/edit'),
        ];
    }
}
