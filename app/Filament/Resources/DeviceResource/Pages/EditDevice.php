<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Jobs\DeviceMonitoringJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDevice extends EditRecord
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test')
                ->label('Test Device')
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function () {
                    DeviceMonitoringJob::dispatch($this->record);
                    
                    Notification::make()
                        ->title('Test Started')
                        ->body('Device monitoring test has been initiated.')
                        ->success()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
}
