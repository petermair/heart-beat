<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServerResource\Pages;
use App\Http\Integrations\ThingsBoardHttp\Requests\Devices\DeviceCreateHttpRequest;
use App\Http\Integrations\ThingsBoardHttp\Requests\Devices\DeviceDeleteHttpRequest;
use App\Http\Integrations\ThingsBoardHttp\Requests\Devices\DevicesHttpRequest;
use App\Http\Integrations\ThingsBoardHttp\Requests\LoginHttpRequest;
use App\Http\Integrations\ThingsBoardHttp\ThingsBoardHttp;
use App\Models\Server;
use App\Models\ServerType;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

class ServerResource extends Resource
{
    protected static ?string $model = Server::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 1;

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
                            ->addable(fn ($get) => ! empty($get('server_type_id')))
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
                Action::make('manage_devices')
                    ->icon('heroicon-o-device-tablet')
                    ->color('success')
                    ->visible(fn (Server $record) => $record->serverType->name === 'ThingsBoard')
                    ->form([
                        Forms\Components\Placeholder::make('existing_devices')
                            ->label('Existing Devices')
                            ->content(function (Server $record) {
                                try {
                                    $client = new ThingsBoardHttp(
                                        baseUrl: $record->url
                                    );

                                    $loginResponse = $client->send(new LoginHttpRequest([
                                        'username' => $record->credentials['username'],
                                        'password' => $record->credentials['password'],
                                    ]));
                                    if (! $loginResponse->successful()) {
                                        return 'Failed to authenticate with ThingsBoard';
                                    }

                                    $devicesResponse = $client->send(new DevicesHttpRequest);
                                    if (! $devicesResponse->successful()) {
                                        return 'Failed to fetch devices';
                                    }

                                    $devices = $devicesResponse->json('data');
                                    if (empty($devices)) {
                                        return 'No devices found';
                                    }

                                    $recordId = $record->id;

                                    return view('filament.components.devices-list', compact('devices', 'recordId'))->render();

                                } catch (\Exception $e) {
                                    return "Error: {$e->getMessage()}";
                                }
                            })->columnSpanFull(),
                        Forms\Components\TextInput::make('device_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Device Name'),
                        Forms\Components\TextInput::make('device_type')
                            ->required()
                            ->default('default')
                            ->maxLength(255)
                            ->label('Device Type'),
                        Forms\Components\TextInput::make('label')
                            ->maxLength(255)
                            ->label('Label (Optional)'),
                    ])
                    ->modalHeading('Manage ThingsBoard Device')
                    ->modalSubmitActionLabel('Create Device')
                    ->action(function (array $data, Server $record): void {
                        $client = new ThingsBoardHttp(
                            baseUrl: $record->url
                        );

                        try {
                            $loginResponse = $client->send(new LoginHttpRequest([
                                'username' => $record->credentials['username'],
                                'password' => $record->credentials['password'],
                            ]));
                            if (! $loginResponse->successful()) {
                                throw new \Exception('Failed to authenticate with ThingsBoard');
                            }

                            $createResponse = $client->send(new DeviceCreateHttpRequest([
                                'name' => $data['device_name'],
                                'type' => $data['device_type'],
                                'label' => $data['label'] ?? null,
                            ]));

                            if (! $createResponse->successful()) {
                                throw new \Exception('Failed to create device');
                            }

                            Notification::make()
                                ->success()
                                ->title('Device Created')
                                ->body("Successfully created device {$data['device_name']}")
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Create Device')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                Action::make('delete_device')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to delete this device? This action cannot be undone.')
                    ->action(function (array $data, Server $record): void {
                        $client = new ThingsBoardHttp(
                            baseUrl: $record->url
                        );

                        try {
                            $loginResponse = $client->send(new LoginHttpRequest([
                                'username' => $record->credentials['username'],
                                'password' => $record->credentials['password'],
                            ]));
                            if (! $loginResponse->successful()) {
                                throw new \Exception('Failed to authenticate with ThingsBoard');
                            }

                            $deleteResponse = $client->send(new DeviceDeleteHttpRequest(
                                deviceId: $data['deviceId']
                            ));

                            if (! $deleteResponse->successful()) {
                                throw new \Exception('Failed to delete device');
                            }

                            Notification::make()
                                ->success()
                                ->title('Device Deleted')
                                ->body('Successfully deleted the device')
                                ->send();

                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to Delete Device')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
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

    protected static function resetDynamicFields($serverTypeId, callable $set): void
    {
        $set('credentials', []);
        $set('settings', []);
    }

    protected static function getCredentialsHelperText($serverTypeId): string
    {
        if (! $serverTypeId) {
            return 'Select a server type first';
        }

        $serverType = ServerType::find($serverTypeId);
        if (! $serverType) {
            return '';
        }

        $required = implode(', ', $serverType->required_credentials ?? []);

        return "Required credentials: {$required}";
    }

    protected static function getSettingsHelperText($serverTypeId): string
    {
        if (! $serverTypeId) {
            return 'Select a server type first';
        }

        $serverType = ServerType::find($serverTypeId);
        if (! $serverType) {
            return '';
        }

        $required = implode(', ', $serverType->required_settings ?? []);

        return "Required settings: {$required}";
    }

    protected static function validateCredentials($serverTypeId): callable
    {
        return function ($value) use ($serverTypeId) {
            if (! $serverTypeId) {
                return true;
            }

            $serverType = ServerType::find($serverTypeId);
            if (! $serverType || empty($serverType->required_credentials)) {
                return true;
            }

            if (! is_array($value)) {
                return 'Invalid credentials format';
            }

            $providedKeys = array_keys($value);
            $missingKeys = array_diff($serverType->required_credentials, $providedKeys);

            if (! empty($missingKeys)) {
                $missing = implode(', ', $missingKeys);

                return "Missing required credentials: {$missing}";
            }

            return true;
        };
    }

    protected static function validateSettings($serverTypeId): callable
    {
        return function ($value) use ($serverTypeId) {
            if (! $serverTypeId) {
                return true;
            }

            $serverType = ServerType::find($serverTypeId);
            if (! $serverType || empty($serverType->required_settings)) {
                return true;
            }

            if (! is_array($value)) {
                return 'Invalid settings format';
            }

            $providedKeys = array_keys($value);
            $missingKeys = array_diff($serverType->required_settings, $providedKeys);

            if (! empty($missingKeys)) {
                $missing = implode(', ', $missingKeys);

                return "Missing required settings: {$missing}";
            }

            return true;
        };
    }
}
