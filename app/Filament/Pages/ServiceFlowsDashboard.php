<?php

namespace App\Filament\Pages;

use App\Models\TestScenario;
use App\Helpers\StatusHelper;
use App\Enums\ServiceType;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Form;

class ServiceFlowsDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Service Flows';
    protected static ?string $title = 'Service Flows';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.pages.service-flows-dashboard';

    protected function getStatusDescription(TestScenario $scenario): string
    {
        $services = [
            ServiceType::THINGSBOARD->label() => $scenario->thingsboard_status,
            ServiceType::MQTT->label() => $scenario->mqtt_status,
            ServiceType::LORATX->label() => $scenario->loratx_status,
            ServiceType::CHIRPSTACK->label() => $scenario->chirpstack_status,
            ServiceType::LORARX->label() => $scenario->lorarx_status,
        ];

        return StatusHelper::getStatusDescription($services);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Scenarios')
                ->tabs($this->getTabs())
        ]);
    }

    public function getTabs(): array
    {
        $scenarios = TestScenario::all();
        
        $tabs = [];
        foreach ($scenarios as $scenario) {
            $statusDescription = $this->getStatusDescription($scenario);
            
            $tabs[$scenario->id] = Tab::make($scenario->name)
                ->schema([
                    Section::make('Downlink Flow (ThingsBoard to ChirpStack)')
                        ->description($statusDescription)
                        ->schema([
                            Grid::make(5)
                                ->schema([
                                    // ThingsBoard
                                    Placeholder::make('thingsboard')
                                        ->label(ServiceType::THINGSBOARD->label())
                                        ->content(StatusHelper::formatStatus($scenario->thingsboard_status)),

                                    // MQTT Broker 1
                                    Placeholder::make('mqtt_broker_1')
                                        ->label(ServiceType::MQTT->label())
                                        ->content(StatusHelper::formatStatus($scenario->mqtt_status)),

                                    // LoRa TX
                                    Placeholder::make('lora_tx')
                                        ->label(ServiceType::LORATX->label())
                                        ->content(StatusHelper::formatStatus($scenario->loratx_status)),

                                    // MQTT Broker 2
                                    Placeholder::make('mqtt_broker_2')
                                        ->label(ServiceType::MQTT->label())
                                        ->content(StatusHelper::formatStatus($scenario->mqtt_status)),

                                    // ChirpStack
                                    Placeholder::make('chirpstack')
                                        ->label(ServiceType::CHIRPSTACK->label())
                                        ->content(StatusHelper::formatStatus($scenario->chirpstack_status)),
                                ])
                        ]),
                        
                    Section::make('Uplink Flow (ChirpStack to ThingsBoard)')
                        ->description($statusDescription)
                        ->schema([
                            Grid::make(5)
                                ->schema([
                                    // ChirpStack
                                    Placeholder::make('chirpstack_up')
                                        ->label(ServiceType::CHIRPSTACK->label())
                                        ->content(StatusHelper::formatStatus($scenario->chirpstack_status)),

                                    // MQTT Broker 1
                                    Placeholder::make('mqtt_broker_up_1')
                                        ->label(ServiceType::MQTT->label())
                                        ->content(StatusHelper::formatStatus($scenario->mqtt_status)),

                                    // LoRa RX
                                    Placeholder::make('lora_rx')
                                        ->label(ServiceType::LORARX->label())
                                        ->content(StatusHelper::formatStatus($scenario->lorarx_status)),

                                    // MQTT Broker 2
                                    Placeholder::make('mqtt_broker_up_2')
                                        ->label(ServiceType::MQTT->label())
                                        ->content(StatusHelper::formatStatus($scenario->mqtt_status)),

                                    // ThingsBoard
                                    Placeholder::make('thingsboard_up')
                                        ->label(ServiceType::THINGSBOARD->label())
                                        ->content(StatusHelper::formatStatus($scenario->thingsboard_status)),
                                ])
                        ])
                ]);
        }
        
        return $tabs;
    }

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }
}
