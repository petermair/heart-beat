<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NotificationSettingResource\Pages;
use App\Models\NotificationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class NotificationSettingResource extends Resource
{
    protected static ?string $model = NotificationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 30;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Settings')
                    ->schema([
                        Forms\Components\Select::make('channel')
                            ->options(fn () => (new NotificationSetting())->getAvailableChannels())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $setting = new NotificationSetting(['channel' => $state]);
                                $set('configuration', $setting->getDefaultConfiguration());
                                $set('conditions', $setting->getDefaultConditions());
                            }),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Channel Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('configuration')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addable()
                            ->deletable(),
                    ]),

                Forms\Components\Section::make('Notification Conditions')
                    ->schema([
                        Forms\Components\KeyValue::make('conditions')
                            ->keyLabel('Condition')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addable()
                            ->deletable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('notifiable_type')
                    ->label('Type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notifiable.name')
                    ->label('Target')
                    ->searchable(),
                Tables\Columns\TextColumn::make('channel')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('channel')
                    ->options(fn () => (new NotificationSetting())->getAvailableChannels()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificationSettings::route('/'),
            'create' => Pages\CreateNotificationSetting::route('/create'),
            'edit' => Pages\EditNotificationSetting::route('/{record}/edit'),
        ];
    }
}
