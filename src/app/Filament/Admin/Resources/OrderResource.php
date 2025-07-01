<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Filament\Admin\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

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

                Forms\Components\Section::make('Bukti Pembayaran Client')
                    ->schema([
                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Bukti Transfer')
                            ->image()
                            ->directory('payment-proofs')
                            ->downloadable()
                            ->openable()
                            ->disabled()
                            ->helperText('Bukti transfer diupload oleh client'),
                        
                        Forms\Components\Placeholder::make('proof_preview')
                            ->label('Pratinjau Bukti')
                            ->content(function (Order $record) {
                                if (!$record->payment_proof) {
                                    return 'Tidak ada bukti transfer';
                                }
                                
                                $url = Storage::url($record->payment_proof);
                                return new HtmlString(
                                    '<div class="flex justify-center">
                                        <img src="'.$url.'" 
                                             class="max-w-full h-64 object-contain border rounded-lg"
                                             onerror="this.style.display=\'none\'">
                                    </div>'
                                );
                            })
                            ->hidden(fn (?Order $record) => !$record?->payment_proof)
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn (?Order $record): bool => !$record?->payment_proof),

                Forms\Components\Section::make('Verifikasi Pembayaran')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status Pembayaran')
                            ->options([
                                'proses' => 'Menunggu Verifikasi',
                                'dibayar' => 'Pembayaran Berhasil',
                                'ditolak' => 'Pembayaran Ditolak',
                            ])
                            ->required()
                            ->native(false),
                            
                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Catatan Verifikasi')
                            ->columnSpanFull(),
                    ])
                    ->hidden(fn (?Order $record): bool => $record?->status === 'pending'),

                Forms\Components\Section::make('Aksi')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('print_invoice')
                                ->label('Cetak Invoice')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->visible(fn (?Order $record): bool => $record?->status === 'dibayar')
                                ->url(fn (Order $record): string => route('invoice.download', $record))
                                ->openUrlInNewTab(),
                        ])
                        ->hidden(fn (?Order $record): bool => $record?->status !== 'dibayar'),
                    ])
                    ->hidden(fn (?Order $record): bool => $record?->status !== 'dibayar'),
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
                    ->description(fn(Order $record): string => $record->vehicle->merk)
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
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'bank_transfer' => 'Transfer Bank',
                        default => '-'
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'bank_transfer' => 'info',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('payment_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->alignEnd(),

                Tables\Columns\ImageColumn::make('payment_proof')
                    ->label('Bukti Pembayaran')
                    ->disk('public')
                    ->width(100)
                    ->height(100)
                    ->extraImgAttributes(['class' => 'rounded-lg border border-gray-200']),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Menunggu Pembayaran',
                        'proses' => 'Menunggu Verifikasi',
                        'dibayar' => 'Diverifikasi',
                        'ditolak' => 'Ditolak',
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'proses' => 'primary',
                        'dibayar' => 'success',
                        'ditolak' => 'danger',
                    }),
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
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('verify')
                    ->label('Verifikasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Order $record): bool => $record->status === 'proses')
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
                    ->visible(fn(Order $record): bool => $record->status === 'proses')
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

                Tables\Actions\Action::make('print_invoice')
                    ->label('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('success')
                    ->visible(fn(Order $record): bool => $record->status === 'dibayar')
                    ->url(fn (Order $record): string => route('invoice.download', $record))
                    ->openUrlInNewTab(),
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
                            ->color(fn(string $state): string => match ($state) {
                                'pending' => 'warning',
                                'proses' => 'primary',
                                'dibayar' => 'success',
                                'ditolak' => 'danger',
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Bukti Pembayaran')
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_proof')
                            ->label('')
                            ->disk('public')
                            ->height(400)
                            ->extraImgAttributes([
                                'class' => 'rounded-lg border border-gray-200 object-contain mx-auto',
                                'style' => 'max-width: 100%'
                            ])
                            ->hidden(fn(Order $record): bool => !$record->payment_proof),
                            
                        Infolists\Components\TextEntry::make('payment_method')
                            ->label('Metode Pembayaran'),
                        Infolists\Components\TextEntry::make('payment_amount')
                            ->label('Jumlah Dibayar')
                            ->money('IDR'),
                        Infolists\Components\TextEntry::make('payment_date')
                            ->label('Tanggal Pembayaran')
                            ->date(),
                    ])
                    ->hidden(fn(Order $record): bool => $record->status === 'pending')
                    ->columns(2),

                Infolists\Components\Section::make('Verifikasi Pembayaran')
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_verified_at')
                            ->label('Diverifikasi Pada')
                            ->dateTime()
                            ->hidden(fn(Order $record): bool => $record->status !== 'dibayar'),
                        Infolists\Components\TextEntry::make('payment_rejected_at')
                            ->label('Ditolak Pada')
                            ->dateTime()
                            ->hidden(fn(Order $record): bool => $record->status !== 'ditolak'),
                        Infolists\Components\TextEntry::make('payment_notes')
                            ->label('Catatan')
                            ->columnSpanFull()
                            ->hidden(fn(Order $record): bool => empty($record->payment_notes)),
                    ])
                    ->hidden(fn(Order $record): bool => $record->status === 'pending'),

                Infolists\Components\Section::make('Aksi')
                    ->schema([
                        Infolists\Components\Actions::make([
                            Infolists\Components\Actions\Action::make('print_invoice')
                                ->label('Cetak Invoice')
                                ->icon('heroicon-o-printer')
                                ->color('success')
                                ->url(fn (Order $record): string => route('invoice.download', $record))
                                ->hidden(fn(Order $record): bool => $record->status !== 'dibayar'),
                        ]),
                    ])
                    ->hidden(fn(Order $record): bool => $record->status !== 'dibayar'),
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