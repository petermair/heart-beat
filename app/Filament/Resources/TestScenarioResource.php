<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestScenarioResource\Pages;
use App\Jobs\ExecuteTestScenarioJob;
use App\Models\TestScenario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class TestScenarioResource extends Resource
{
    protected static ?string $model = TestScenario::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 20;

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
                        Forms\Components\Select::make('device_id')
                            ->relationship('device', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('test_type')
                            ->options(fn () => (new TestScenario())->getTestTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $scenario = new TestScenario(['test_type' => $state]);
                                $set('test_configuration', $scenario->getDefaultConfiguration());
                            }),
                    ]),

                Forms\Components\Section::make('Test Configuration')
                    ->schema([
                        Forms\Components\KeyValue::make('test_configuration')
                            ->keyLabel('Parameter')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addable()
                            ->deletable(),
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('interval_seconds')
                                    ->label('Interval (seconds)')
                                    ->numeric()
                                    ->default(300)
                                    ->required(),
                                Forms\Components\TextInput::make('timeout_seconds')
                                    ->label('Timeout (seconds)')
                                    ->numeric()
                                    ->default(30)
                                    ->required(),
                                Forms\Components\TextInput::make('max_retries')
                                    ->numeric()
                                    ->default(3)
                                    ->required(),
                            ]),
                    ]),

                Forms\Components\Section::make('Notification Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('notification_settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->reorderable()
                            ->addable()
                            ->deletable(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('device.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('test_type')
                    ->sortable(),
                Tables\Columns\TextColumn::make('interval_seconds')
                    ->label('Interval')
                    ->formatStateUsing(fn (int $state) => "{$state}s")
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('test_type')
                    ->options(fn () => (new TestScenario())->getTestTypes()),
                Tables\Filters\SelectFilter::make('device')
                    ->relationship('device', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
                        ->action(fn (Builder $query) => $query->update(['is_active' => true]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->action(fn (Builder $query) => $query->update(['is_active' => false]))
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
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
            'index' => Pages\ListTestScenarios::route('/'),
            'create' => Pages\CreateTestScenario::route('/create'),
            'edit' => Pages\EditTestScenario::route('/{record}/edit'),
        ];
    }
}
