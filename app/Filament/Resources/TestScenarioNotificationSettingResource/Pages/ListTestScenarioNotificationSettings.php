<?php

namespace App\Filament\Resources\TestScenarioNotificationSettingResource\Pages;

use App\Filament\Resources\TestScenarioNotificationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTestScenarioNotificationSettings extends ListRecords
{
    protected static string $resource = TestScenarioNotificationSettingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
