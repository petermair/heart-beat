<?php

namespace App\Filament\Resources\TestScenarioNotificationSettingResource\Pages;

use App\Filament\Resources\TestScenarioNotificationSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTestScenarioNotificationSetting extends EditRecord
{
    protected static string $resource = TestScenarioNotificationSettingResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
