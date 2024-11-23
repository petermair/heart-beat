<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class MonitoringResultsRelationManager extends RelationManager
{
    protected static string $relationship = 'monitoringResults';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('success')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('test_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'gray',
                        'manual' => 'primary',
                        'api' => 'success',
                        default => 'warning',
                    }),
                Tables\Columns\IconColumn::make('chirpstack_status')
                    ->label('ChirpStack')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('chirpstack_response_time')
                    ->label('CS Time (ms)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('thingsboard_status')
                    ->label('ThingsBoard')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('thingsboard_response_time')
                    ->label('TB Time (ms)')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('error_message')
                    ->label('Error')
                    ->wrap()
                    ->limit(50),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('test_type')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'manual' => 'Manual',
                        'api' => 'API',
                    ]),
                Tables\Filters\TernaryFilter::make('success')
                    ->label('Status'),
            ])
            ->actions([
                // No actions needed for monitoring results
            ])
            ->bulkActions([
                // No bulk actions needed
            ])
            ->paginated([10, 25, 50, 100]);
    }
}
