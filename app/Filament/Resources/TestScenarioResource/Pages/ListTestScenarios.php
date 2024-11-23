<?php

namespace App\Filament\Resources\TestScenarioResource\Pages;

use App\Filament\Resources\TestScenarioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestScenarios extends ListRecords
{
    protected static string $resource = TestScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
