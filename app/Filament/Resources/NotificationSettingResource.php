<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationSettingResource\Pages;
use App\Models\NotificationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NotificationSettingResource extends Resource
{
    protected static ?string $model = NotificationSetting::class;
    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 11;

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
                        Forms\Components\Select::make('notification_type_id')
                            ->relationship('notificationType', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('configuration', [])),
                        Forms\Components\Toggle::make('is_active')
                            ->default(true),
                    ]),
                Forms\Components\Section::make('Configuration')
                    ->schema(function (Forms\Get $get) {
                        $notificationType = NotificationType::find($get('notification_type_id'));
                        if (!$notificationType) {
                            return [
                                Forms\Components\Placeholder::make('configuration_help')
                                    ->content('Please select a notification type first.'),
                            ];
                        }

                        $schema = $notificationType->configuration_schema ?? [];
                        $fields = [];

                        foreach ($schema as $fieldName => $rules) {
                            $field = match ($rules['type'] ?? 'text') {
                                'number' => Forms\Components\TextInput::make("configuration.{$fieldName}")
                                    ->numeric()
                                    ->label($rules['label'] ?? $fieldName),
                                'url' => Forms\Components\TextInput::make("configuration.{$fieldName}")
                                    ->url()
                                    ->label($rules['label'] ?? $fieldName),
                                'email' => Forms\Components\TextInput::make("configuration.{$fieldName}")
                                    ->email()
                                    ->label($rules['label'] ?? $fieldName),
                                'select' => Forms\Components\Select::make("configuration.{$fieldName}")
                                    ->options($rules['options'] ?? [])
                                    ->label($rules['label'] ?? $fieldName),
                                default => Forms\Components\TextInput::make("configuration.{$fieldName}")
                                    ->label($rules['label'] ?? $fieldName),
                            };

                            if ($rules['required'] ?? false) {
                                $field->required();
                            }

                            if ($rules['help'] ?? false) {
                                $field->helperText($rules['help']);
                            }

                            $fields[] = $field;
                        }

                        return $fields;
                    }),
                Forms\Components\Section::make('Test Scenarios')
                    ->schema([
                        Forms\Components\Select::make('test_scenarios')
                            ->relationship('testScenarios', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable(),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notificationType.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('test_scenarios_count')
                    ->counts('testScenarios')
                    ->label('Test Scenarios'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('notification_type')
                    ->relationship('notificationType', 'name'),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationSettings::route('/'),
            'create' => Pages\CreateNotificationSetting::route('/create'),
            'edit' => Pages\EditNotificationSetting::route('/{record}/edit'),
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

    private static function getConfigurationHelp(?int $notificationTypeId): string
    {
        if (!$notificationTypeId) {
            return 'Select a notification type to see configuration options.';
        }

        $type = \App\Models\NotificationType::find($notificationTypeId);
        if (!$type) {
            return 'Invalid notification type selected.';
        }

        $schema = $type->configuration_schema ?? [];
        if (empty($schema)) {
            return 'No configuration required for this notification type.';
        }

        $help = "Required fields for {$type->name}:\n";
        foreach ($schema as $field => $rules) {
            $required = ($rules['required'] ?? false) ? '(Required)' : '(Optional)';
            $default = isset($rules['default']) ? " [Default: {$rules['default']}]" : '';
            $description = $rules['description'] ?? '';
            $help .= "- {$field} {$required}{$default}: {$description}\n";
        }

        return $help;
    }
}
