<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Models\Server;
use App\Models\ServerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;
    protected static ?string $navigationIcon = 'heroicon-o-server';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Server Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('server_type_id')
                            ->label('Server Type')
                            ->required()
                            ->options(ServerType::pluck('name', 'id'))
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => static::resetDynamicFields($state, $set)),
                        Forms\Components\TextInput::make('url')
                            ->required()
                            ->url()
                            ->maxLength(255),
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
                    ])->columns(2),

                Forms\Components\Section::make('Authentication')
                    ->schema([
                        Forms\Components\KeyValue::make('credentials')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addable(fn ($get) => !empty($get('server_type_id')))
                            ->deletable()
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->helperText(fn ($get) => static::getCredentialsHelperText($get('server_type_id')))
                            ->rules(['required', fn ($get) => static::validateCredentials($get('server_type_id'))]),
                    ]),

                Forms\Components\Section::make('Additional Settings')
                    ->schema([
                        Forms\Components\KeyValue::make('settings')
                            ->keyLabel('Setting')
                            ->valueLabel('Value')
                            ->addable(fn ($get) => !empty($get('server_type_id')))
                            ->deletable()
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->helperText(fn ($get) => static::getSettingsHelperText($get('server_type_id')))
                            ->rules(['required', fn ($get) => static::validateSettings($get('server_type_id'))]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serverType.name')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'ThingsBoard' => 'success',
                        'ChirpStack' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('url')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('monitoring_interval')
                    ->suffix(' seconds'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('server_type_id')
                    ->label('Server Type')
                    ->options(ServerType::pluck('name', 'id')),
                Tables\Filters\TernaryFilter::make('is_active'),
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
            'index' => Pages\ListServers::route('/'),
            'create' => Pages\CreateServer::route('/create'),
            'edit' => Pages\EditServer::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static function resetDynamicFields($serverTypeId, callable $set): void
    {
        $set('credentials', []);
        $set('settings', []);
    }

    protected static function getCredentialsHelperText($serverTypeId): string
    {
        if (!$serverTypeId) {
            return 'Select a server type first';
        }

        $serverType = ServerType::find($serverTypeId);
        if (!$serverType) {
            return '';
        }

        $required = implode(', ', $serverType->required_credentials ?? []);
        return "Required credentials: {$required}";
    }

    protected static function getSettingsHelperText($serverTypeId): string
    {
        if (!$serverTypeId) {
            return 'Select a server type first';
        }

        $serverType = ServerType::find($serverTypeId);
        if (!$serverType) {
            return '';
        }

        $required = implode(', ', $serverType->required_settings ?? []);
        return "Required settings: {$required}";
    }

    protected static function validateCredentials($serverTypeId): callable
    {
        return function ($value) use ($serverTypeId) {
            if (!$serverTypeId) {
                return true;
            }

            $serverType = ServerType::find($serverTypeId);
            if (!$serverType || empty($serverType->required_credentials)) {
                return true;
            }

            $providedKeys = array_keys($value ?? []);
            $missingKeys = array_diff($serverType->required_credentials, $providedKeys);

            if (!empty($missingKeys)) {
                $missing = implode(', ', $missingKeys);
                return "Missing required credentials: {$missing}";
            }

            return true;
        };
    }

    protected static function validateSettings($serverTypeId): callable
    {
        return function ($value) use ($serverTypeId) {
            if (!$serverTypeId) {
                return true;
            }

            $serverType = ServerType::find($serverTypeId);
            if (!$serverType || empty($serverType->required_settings)) {
                return true;
            }

            $providedKeys = array_keys($value ?? []);
            $missingKeys = array_diff($serverType->required_settings, $providedKeys);

            if (!empty($missingKeys)) {
                $missing = implode(', ', $missingKeys);
                return "Missing required settings: {$missing}";
            }

            return true;
        };
    }
}
