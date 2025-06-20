<?php

namespace App\Filament\Admin\Resources\SalesReportResource\Pages;

use App\Filament\Admin\Resources\SalesReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesReport extends EditRecord
{
    protected static string $resource = SalesReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
