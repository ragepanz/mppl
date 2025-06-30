<?php

namespace App\Filament\Client\Resources\OrderResource\Pages;

use App\Filament\Client\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('pay')
                ->label('Bayar')
                ->icon('heroicon-o-credit-card')
                ->url(fn () => static::getResource()::getUrl('pay', ['record' => $this->record]))
                ->visible(fn () => $this->record->status === 'pending'),
        ];

        $this->redirect(static::getResource()::getUrl('index'));
    }
}