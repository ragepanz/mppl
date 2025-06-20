<?php

namespace App\Filament\Client\Resources\VehicleResource\Pages;

use App\Filament\Client\Resources\VehicleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVehicle extends ViewRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions needed for client view
        ];
    }
}