<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Pesanan'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Semua Pesanan')
                ->icon('heroicon-o-list-bullet')
                ->badge($this->getModel()::count()),
            
            'pending' => Tab::make('Menunggu Pembayaran')
                ->icon('heroicon-o-clock')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
                ->badge($this->getModel()::where('status', 'pending')->count()),
            
            'dibayar' => Tab::make('Sudah Dibayar')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'dibayar'))
                ->badge($this->getModel()::where('status', 'dibayar')->count()),
            
            'diproses' => Tab::make('Sedang Diproses')
                ->icon('heroicon-o-cog')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'diproses'))
                ->badge($this->getModel()::where('status', 'diproses')->count()),
            

        ];
    }
}