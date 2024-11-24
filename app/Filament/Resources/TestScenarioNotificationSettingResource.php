<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestScenarioNotificationSettingResource\Pages;
use App\Models\TestScenarioNotificationSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use InvadersXX\FilamentJsoneditor\Forms\JSONEditor;

class TestScenarioNotificationSettingResource extends Resource
{
    protected static ?string $model = TestScenarioNotificationSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationGroup = 'Test Scenarios';

    protected static ?string $navigationLabel = 'Notifications';

    protected static ?string $modelLabel = 'Notification';

    protected static ?string $pluralModelLabel = 'Notifications';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('test_scenario_id')
                    ->relationship('testScenario', 'name')
                    ->required(),
                Forms\Components\Select::make('notification_setting_id')
                    ->relationship('notificationSetting', 'name')
                    ->required(),
                JSONEditor::make('settings')
                    ->label('Configuration')
                    ->required()
                    ->columnSpan('full'),

                Forms\Components\DateTimePicker::make('last_notification_at')
                    ->label('Last Notification At'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('testScenario.name')
                    ->label('Test Scenario')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('notificationSetting.name')
                    ->label('Notification Setting')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_notification_at')
                    ->label('Last Notification')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTestScenarioNotificationSettings::route('/test-scenarios/notifications'),
            'create' => Pages\CreateTestScenarioNotificationSetting::route('/test-scenarios/notifications/create'),
            'edit' => Pages\EditTestScenarioNotificationSetting::route('/test-scenarios/notifications/{record}/edit'),
        ];
    }
}
