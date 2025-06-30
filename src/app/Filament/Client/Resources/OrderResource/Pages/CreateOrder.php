<?php

namespace App\Filament\Client\Resources\OrderResource\Pages;

use App\Filament\Client\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('pay', ['record' => $this->record]);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pesanan Berhasil Dibuat!';
    }
}