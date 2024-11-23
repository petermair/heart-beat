<?php

namespace App\Filament\Resources\TestScenarioResource\Pages;

use App\Filament\Resources\TestScenarioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestScenario extends EditRecord
{
    protected static string $resource = TestScenarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
