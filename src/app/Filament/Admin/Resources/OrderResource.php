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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Set;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Route;


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
                Section::make('Informasi Pesanan')
                    ->description('Detail pemesanan kendaraan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // Input Pelanggan
                                Forms\Components\Select::make('user_id')
                                    ->label('Pelanggan')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->options(User::all()->pluck('name', 'id'))
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\TextInput::make('email')
                                            ->email()
                                            ->required()
                                            ->maxLength(255),
                                    ]),
                                
                                // Input Kendaraan (Auto-price)
                                Forms\Components\Select::make('vehicle_id')
                                    ->label('Kendaraan')
                                    ->relationship('vehicle', 'nama')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $vehicle = Vehicle::find($state);
                                        if ($vehicle) {
                                            $set('total_harga', $vehicle->harga);
                                        }
                                    })
                                    ->options(Vehicle::all()->mapWithKeys(function ($vehicle) {
                                        return [
                                            $vehicle->id => "{$vehicle->nama} ({$vehicle->merk}) - Rp " . number_format($vehicle->harga, 0, ',', '.')
                                        ];
                                    })),
                                
                                // Tanggal Pesan
                                Forms\Components\DatePicker::make('tanggal_order')
                                    ->label('Tanggal Pesan')
                                    ->required()
                                    ->default(now())
                                    ->maxDate(now()),
                                
                                // Status
                                Forms\Components\Select::make('status')
                                    ->required()
                                    ->options([
                                        'pending' => 'Pending',
                                        'proses' => 'Proses',
                                        'dibayar' => 'Pembayaran Berhasil',
                                    ])
                                    ->default('pending'),
                                
                                // Total Harga (Auto-set dari kendaraan)
                                Forms\Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    //->thousandsSeparator('.')
                                    ->inputMode('decimal')
                                    ->readOnly()
                                    ->dehydrated(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Kolom Pelanggan
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pelanggan')
                    ->searchable()
                    ->sortable(),
                
                // Kolom Kendaraan
                Tables\Columns\TextColumn::make('vehicle.nama')
                    ->label('Kendaraan')
                    ->description(fn (Order $record) => $record->vehicle->merk)
                    ->searchable()
                    ->sortable(),
                
                // Kolom Tanggal
                Tables\Columns\TextColumn::make('tanggal_order')
                    ->label('Tanggal Pesan')
                    ->date('d M Y')
                    ->sortable(),
                
                // Kolom Harga Kendaraan
                Tables\Columns\TextColumn::make('vehicle.harga')
                    ->label('Harga Kendaraan')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),
                
                // Kolom Total Harga
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->color('primary')
                    ->weight('bold'),
                
                // Kolom Status
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'proses',
                        'success' => 'dibayar',
                    ])
                    ->icons([
                        'heroicon-o-clock' => 'pending',
                        'heroicon-o-arrow-path' => 'proses',
                        'heroicon-o-check-circle' => 'dibayar',
                    ]),
            ])
            ->filters([
                // Filter Status
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'proses' => 'Proses',
                        'dibayar' => 'Pembayaran Berhasil',
                    ]),
                
                // Filter Tanggal
                Tables\Filters\Filter::make('tanggal_order')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
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
                
                // Filter Kendaraan
                Tables\Filters\SelectFilter::make('vehicle_id')
                    ->label('Kendaraan')
                    ->relationship('vehicle', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton(),
            
                Tables\Actions\EditAction::make()
                    ->iconButton(),
            
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            
                Action::make('cetak')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Order $record): string => route('client.invoice.show', $record)) // atau 'admin.invoice.show'
                    ->openUrlInNewTab()
                    ->tooltip('Cetak Invoice'),
            ])
            
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Tandai sebagai Selesai')
                        ->action(fn ($records) => $records->each->update(['status' => 'completed']))
                        ->icon('heroicon-o-check')
                        ->color('success'),
                ]),
            ])
            ->defaultSort('tanggal_order', 'desc')
            ->groups([
                Tables\Grouping\Group::make('tanggal_order')
                    ->label('Berdasarkan Tanggal')
                    ->date()
                    ->collapsible(),
                Tables\Grouping\Group::make('status')
                    ->label('Berdasarkan Status')
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
           // RelationManagers\PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
            //'view' => Pages\ViewOrder::route('/{record}'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}