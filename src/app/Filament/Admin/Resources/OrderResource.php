<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\User;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $navigationLabel = 'Manajemen Pesanan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pelanggan')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                
                                Forms\Components\Select::make('vehicle_id')
                                    ->label('Kendaraan')
                                    ->relationship('vehicle', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        $vehicle = Vehicle::find($state);
                                        if ($vehicle) {
                                            $set('total_harga', $vehicle->harga);
                                        }
                                    }),
                                
                                Forms\Components\DatePicker::make('tanggal_order')
                                    ->label('Tanggal Pesan')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now()),
                                
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Menunggu Pembayaran',
                                        'proses' => 'Menunggu Verifikasi',
                                        'dibayar' => 'Pembayaran Berhasil',
                                        'ditolak' => 'Pembayaran Ditolak',
                                    ])
                                    ->default('pending')
                                    ->live(),
                                
                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'bank_transfer' => 'Transfer Bank',
                                'e_wallet' => 'E-Wallet',
                                'virtual_account' => 'Virtual Account',
                                'qris' => 'QRIS',
                                'cash' => 'Tunai',
                            ])
                            ->required(fn (?Order $record): bool => $record?->status !== 'pending'),
                            
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Jumlah Dibayar')
                            ->prefix('Rp')
                            ->numeric()
                            ->required(fn (?Order $record): bool => $record?->status !== 'pending'),
                            
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->required(fn (?Order $record): bool => $record?->status !== 'pending'),
                            
                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->directory('payment-proofs')
                            ->image()
                            ->downloadable()
                            ->openable()
                            ->required(fn (?Order $record): bool => $record?->status !== 'pending'),
                            
                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Catatan Pembayaran')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->hidden(fn (?Order $record): bool => $record?->status === 'pending'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('vehicle.nama')
                    ->label('Kendaraan')
                    ->description(fn (Order $record): string => $record->vehicle->merk)
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('tanggal_order')
                    ->label('Tanggal Pesan')
                    ->date('d M Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'bank_transfer' => 'Transfer Bank',
                        'e_wallet' => 'E-Wallet',
                        'virtual_account' => 'Virtual Account',
                        'qris' => 'QRIS',
                        'cash' => 'Tunai',
                        default => '-'
                    })
                    ->color(fn (string $state): string => match($state) {
                        'bank_transfer' => 'info',
                        'e_wallet' => 'primary',
                        'virtual_account' => 'warning',
                        'qris' => 'success',
                        'cash' => 'gray',
                        default => 'gray'
                    }),
                
                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->alignEnd(),
                
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Pembayaran',
                        'proses' => 'Menunggu Verifikasi',
                        'dibayar' => 'Diverifikasi',
                        'ditolak' => 'Ditolak',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'proses' => 'primary',
                        'dibayar' => 'success',
                        'ditolak' => 'danger',
                    }),
                
                Tables\Columns\TextColumn::make('payment_verified_at')
                    ->label('Diverifikasi Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('payment_rejected_at')
                    ->label('Ditolak Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'proses' => 'Menunggu Verifikasi',
                        'dibayar' => 'Diverifikasi',
                        'ditolak' => 'Ditolak',
                    ]),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Transfer Bank',
                        'e_wallet' => 'E-Wallet',
                        'virtual_account' => 'Virtual Account',
                        'qris' => 'QRIS',
                        'cash' => 'Tunai',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Order $record): bool => $record->status === 'proses')
                    ->action(function (Order $record): void {
                        $record->update([
                            'status' => 'dibayar',
                            'payment_verified_at' => now(),
                        ]);
                        
                        Notification::make()
                            ->title('Pembayaran Diverifikasi')
                            ->body('Pembayaran telah berhasil diverifikasi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Order $record): bool => $record->status === 'proses')
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $record->update([
                            'status' => 'ditolak',
                            'payment_rejected_at' => now(),
                            'payment_notes' => $data['reason'],
                        ]);
                        
                        Notification::make()
                            ->title('Pembayaran Ditolak')
                            ->body('Alasan: ' . $data['reason'])
                            ->danger()
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('tanggal_order', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Pesanan')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Pelanggan'),
                        Infolists\Components\TextEntry::make('vehicle.nama')
                            ->label('Kendaraan'),
                        Infolists\Components\TextEntry::make('tanggal_order')
                            ->label('Tanggal Pesan')
                            ->date(),
                        Infolists\Components\TextEntry::make('total_harga')
                            ->label('Total Harga')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'proses' => 'primary',
                                'dibayar' => 'success',
                                'ditolak' => 'danger',
                            }),
                    ])
                    ->columns(2),
                
                Infolists\Components\Section::make('Informasi Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Metode Pembayaran'),
                        Infolists\Components\TextEntry::make('payment_amount')
                            ->label('Jumlah Dibayar')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->date(),
                        Infolists\Components\ImageEntry::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->height(300),
                        Infolists\Components\TextEntry::make('payment_notes')
                            ->label('Catatan Pembayaran')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->hidden(fn (Order $record): bool => $record->status === 'pending'),
                
                Infolists\Components\Section::make('Verifikasi Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_verified_at')
                            ->label('Diverifikasi Pada')
                            ->dateTime()
                            ->hidden(fn (Order $record): bool => $record->status !== 'dibayar'),
                        Infolists\Components\TextEntry::make('payment_rejected_at')
                            ->label('Ditolak Pada')
                            ->dateTime()
                            ->hidden(fn (Order $record): bool => $record->status !== 'ditolak'),
                    ])
                    ->hidden(fn (Order $record): bool => $record->status === 'pending'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'proses')->count();
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }
}