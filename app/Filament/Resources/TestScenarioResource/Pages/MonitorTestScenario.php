<?php

namespace App\Filament\Resources\TestScenarioResource\Pages;

use App\Filament\Resources\TestScenarioResource;
use App\Models\TestScenario;
use App\Enums\FlowType;
use App\Enums\ServiceType;
use App\Helpers\StatusHelper;
use Filament\Resources\Pages\Page;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\View\View;

class MonitorTestScenario extends Page
{
    protected static string $resource = TestScenarioResource::class;

    protected static string $view = 'filament.resources.test-scenario-resource.pages.monitor-test-scenario';

    public TestScenario $record;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Overview')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Group::make([
                                    TextEntry::make('name')
                                        ->label('Scenario Name'),
                                    TextEntry::make('description')
                                        ->label('Description'),
                                    TextEntry::make('is_active')
                                        ->label('Status')
                                        ->badge()
                                        ->color(fn ($state) => $state ? 'success' : 'danger'),
                                ])->columnSpan(1),
                                Group::make([
                                    TextEntry::make('mqttDevice.name')
                                        ->label('MQTT Device'),
                                    TextEntry::make('httpDevice.name')
                                        ->label('HTTP Device'),
                                ])->columnSpan(1),
                                Group::make([
                                    TextEntry::make('interval_seconds')
                                        ->label('Interval')
                                        ->formatStateUsing(fn ($state) => "{$state}s"),
                                    TextEntry::make('timeout_seconds')
                                        ->label('Timeout')
                                        ->formatStateUsing(fn ($state) => "{$state}s"),
                                    TextEntry::make('max_retries')
                                        ->label('Max Retries'),
                                ])->columnSpan(1),
                            ]),
                    ]),

                Section::make('Service Flow Status')
                    ->schema([
                        Section::make('Downlink Flow (ThingsBoard to ChirpStack)')
                            ->description(fn (TestScenario $record) => StatusHelper::getStatusDescription([
                                ServiceType::THINGSBOARD->label() => $record->thingsboard_status,
                                ServiceType::MQTT_TB->label() => $record->mqtt_tb_status,
                                ServiceType::LORATX->label() => $record->loratx_status,
                                ServiceType::MQTT_CS->label() => $record->mqtt_cs_status,
                                ServiceType::CHIRPSTACK->label() => $record->chirpstack_status,
                            ]))
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        // ThingsBoard
                                        TextEntry::make('thingsboard_status')
                                            ->label(ServiceType::THINGSBOARD->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // MQTT Broker TB (Downlink)
                                        TextEntry::make('mqtt_tb_status')
                                            ->label(ServiceType::MQTT_TB->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // LoRa TX
                                        TextEntry::make('loratx_status')
                                            ->label(ServiceType::LORATX->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // MQTT Broker CS (Downlink)
                                        TextEntry::make('mqtt_cs_status')
                                            ->label(ServiceType::MQTT_CS->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // ChirpStack
                                        TextEntry::make('chirpstack_status')
                                            ->label(ServiceType::CHIRPSTACK->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),
                                    ])
                            ]),
                            
                        Section::make('Uplink Flow (ChirpStack to ThingsBoard)')
                            ->description(fn (TestScenario $record) => StatusHelper::getStatusDescription([
                                ServiceType::CHIRPSTACK->label() => $record->chirpstack_status,
                                ServiceType::MQTT_CS->label() => $record->mqtt_cs_status,
                                ServiceType::LORARX->label() => $record->lorarx_status,
                                ServiceType::MQTT_TB->label() => $record->mqtt_tb_status,
                                ServiceType::THINGSBOARD->label() => $record->thingsboard_status,
                            ]))
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        // ChirpStack
                                        TextEntry::make('chirpstack_status')
                                            ->label(ServiceType::CHIRPSTACK->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // MQTT Broker CS (Uplink)
                                        TextEntry::make('mqtt_cs_status')
                                            ->label(ServiceType::MQTT_CS->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // LoRa RX
                                        TextEntry::make('lorarx_status')
                                            ->label(ServiceType::LORARX->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // MQTT Broker TB (Uplink)
                                        TextEntry::make('mqtt_tb_status')
                                            ->label(ServiceType::MQTT_TB->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),

                                        // ThingsBoard
                                        TextEntry::make('thingsboard_status')
                                            ->label(ServiceType::THINGSBOARD->label())
                                            ->badge()
                                            ->color(fn ($state) => $this->getStatusColor($state)),
                                    ])
                            ]),
                    ]),
            ]);
    }

    protected function getStatusColor(string $status): string
    {
        return match (strtoupper($status)) {
            'HEALTHY' => 'success',
            'WARNING' => 'warning',
            'CRITICAL' => 'danger',
            default => 'gray',
        };
    }
}
