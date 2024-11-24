<?php

namespace App\Filament\Resources\NotificationTypeResource\Pages;

use App\Filament\Resources\NotificationTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditNotificationType extends EditRecord
{
    protected static string $resource = NotificationTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
