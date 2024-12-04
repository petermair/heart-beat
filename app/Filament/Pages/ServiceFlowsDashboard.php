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
use Filament\Support\Colors\Color;

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
            ServiceType::MQTT_TB->label() => $scenario->mqtt_tb_status,
            ServiceType::LORATX->label() => $scenario->loratx_status,
            ServiceType::MQTT_CS->label() => $scenario->mqtt_cs_status,
            ServiceType::CHIRPSTACK->label() => $scenario->chirpstack_status,
            ServiceType::LORARX->label() => $scenario->lorarx_status,
        ];

        return StatusHelper::getStatusDescription($services);
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

    protected function renderStatus(string $label, string $status): Placeholder
    {
        return Placeholder::make($label)
            ->label($label)
            ->content(view('components.status-badge', [
                'label' => $label,
                'status' => $status,
                'color' => $this->getStatusColor($status),
            ]));
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
                                    $this->renderStatus(
                                        ServiceType::THINGSBOARD->label(),
                                        $scenario->thingsboard_status
                                    ),

                                    // MQTT Broker TB (Downlink)
                                    $this->renderStatus(
                                        ServiceType::MQTT_TB->label(),
                                        $scenario->mqtt_tb_status
                                    ),

                                    // LoRa TX
                                    $this->renderStatus(
                                        ServiceType::LORATX->label(),
                                        $scenario->loratx_status
                                    ),

                                    // MQTT Broker CS (Downlink)
                                    $this->renderStatus(
                                        ServiceType::MQTT_CS->label(),
                                        $scenario->mqtt_cs_status
                                    ),

                                    // ChirpStack
                                    $this->renderStatus(
                                        ServiceType::CHIRPSTACK->label(),
                                        $scenario->chirpstack_status
                                    ),
                                ])
                        ]),
                        
                    Section::make('Uplink Flow (ChirpStack to ThingsBoard)')
                        ->description($statusDescription)
                        ->schema([
                            Grid::make(5)
                                ->schema([
                                    // ChirpStack
                                    $this->renderStatus(
                                        ServiceType::CHIRPSTACK->label(),
                                        $scenario->chirpstack_status
                                    ),

                                    // MQTT Broker CS (Uplink)
                                    $this->renderStatus(
                                        ServiceType::MQTT_CS->label(),
                                        $scenario->mqtt_cs_status
                                    ),

                                    // LoRa RX
                                    $this->renderStatus(
                                        ServiceType::LORARX->label(),
                                        $scenario->lorarx_status
                                    ),

                                    // MQTT Broker TB (Uplink)
                                    $this->renderStatus(
                                        ServiceType::MQTT_TB->label(),
                                        $scenario->mqtt_tb_status
                                    ),

                                    // ThingsBoard
                                    $this->renderStatus(
                                        ServiceType::THINGSBOARD->label(),
                                        $scenario->thingsboard_status
                                    ),
                                ])
                        ]),
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
