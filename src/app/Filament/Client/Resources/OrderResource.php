<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\OrderResource\Pages;
use App\Models\Order;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $modelLabel = 'Pesanan';
    protected static ?string $navigationLabel = 'Pesanan Saya';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id')
                    ->default(auth()->id()),
                    
                Forms\Components\Select::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship(
                        name: 'vehicle',
                        titleAttribute: 'nama',
                        modifyQueryUsing: fn (Builder $query) => $query->where('stok', '>', 0)
                    )
                    ->searchable(['nama', 'merk', 'tipe'])
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                        $vehicle = Vehicle::find($state);
                        if ($vehicle) {
                            $set('total_harga', $vehicle->harga);
                            $set('vehicle_details', 
                                "Merk: {$vehicle->merk}\n" .
                                "Tipe: {$vehicle->tipe}\n" .
                                "Tahun: {$vehicle->tahun}\n" .
                                "Harga: Rp " . Number::format($vehicle->harga, locale: 'id')
                            );
                        }
                    }),
                    
                Forms\Components\Textarea::make('vehicle_details')
                    ->label('Detail Kendaraan')
                    ->columnSpanFull()
                    ->readOnly(),
                    
                Forms\Components\DatePicker::make('tanggal_order')
                    ->label('Tanggal Pesan')
                    ->default(now())
                    ->required()
                    ->disabled(fn (string $operation): bool => $operation === 'edit'),
                    
                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly(),
                    
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan Tambahan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->where('user_id', auth()->id()))
            ->columns([
                Tables\Columns\TextColumn::make('vehicle.nama')
                    ->label('Kendaraan')
                    ->description(fn (Order $record): string => "{$record->vehicle->merk} {$record->vehicle->tipe}")
                    ->searchable(),
                    
                Tables\Columns\ImageColumn::make('vehicle.foto')
                    ->label('Foto')
                    ->disk('public'),
                    
                Tables\Columns\TextColumn::make('tanggal_order')
                    ->label('Tanggal Pesan')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'proses' => 'info',
                        'dibayar' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Menunggu Pembayaran',
                        'proses' => 'Sedang Diproses',
                        'dibayar' => 'Pembayaran Berhasil',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Menunggu Pembayaran',
                        'proses' => 'Sedang Diproses',
                        'dibayar' => 'Pembayaran Berhasil',
                    ]),
                    
                Tables\Filters\Filter::make('tanggal_order')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_order', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_order', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (Order $record): bool => 
                        $record->user_id === auth()->id() && 
                        $record->status === 'pending'),
                        
                Tables\Actions\Action::make('batalkan')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => 
                        $record->user_id === auth()->id() && 
                        $record->status === 'pending')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'dibatalkan']);
                    }),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('user_id', auth()->id());
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return $record->user_id === auth()->id() && $record->status === 'pending';
    }

    public static function canDelete(Model $record): bool
    {
        return false; // Disable delete action
    }

    public static function canView(Model $record): bool
    {
        return $record->user_id === auth()->id();
    }
}