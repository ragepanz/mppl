<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\SalesReportResource\Pages;
use App\Models\Order;
use App\Models\SalesReport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use App\Exports\SalesReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportResource extends Resource
{
    protected static ?string $model = SalesReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $modelLabel = 'Laporan Penjualan';

    protected static ?string $navigationGroup = 'Laporan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Pesanan')
                    ->relationship(
                        name: 'order',
                        modifyQueryUsing: fn (Builder $query) => $query->where('status', 'dibayar')
                    )
                    ->getOptionLabelFromRecordUsing(fn (Order $order) => 
                        "Order #{$order->id} - {$order->vehicle->nama} (Rp " . 
                        Number::format($order->total_harga, locale: 'id') . ")"
                    )
                    ->searchable(['id'])
                    ->required()
                    ->preload(),
                    
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('order', fn ($q) => $q->where('status', 'dibayar')))
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->label('ID Pesanan')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('order.vehicle.nama')
                    ->label('Kendaraan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('order.user.name')
                    ->label('Pelanggan')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('order.total_harga')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('order.tanggal_order')
                    ->label('Tanggal Pesan')
                    ->date('d/m/Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Laporan')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('order.tanggal_order')
                    ->label('Bulan')
                    ->formatStateUsing(fn ($state) => $state->format('F Y'))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('tanggal')
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
                                fn (Builder $query, $date): Builder => $query->whereHas('order', 
                                    fn ($q) => $q->whereDate('tanggal_order', '>=', $date)
                                )
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereHas('order', 
                                    fn ($q) => $q->whereDate('tanggal_order', '<=', $date)
                                )
                            );
                    }),
                    
                Tables\Filters\SelectFilter::make('bulan')
                    ->label('Filter Per Bulan')
                    ->options(function () {
                        $months = [];
                        $reports = SalesReport::with('order')
                            ->whereHas('order', fn ($q) => $q->where('status', 'dibayar'))
                            ->get()
                            ->groupBy(fn ($item) => $item->order->tanggal_order->format('Y-m'));
                        
                        foreach ($reports as $month => $items) {
                            $months[$month] = \Carbon\Carbon::parse($month)->translatedFormat('F Y');
                        }
                        
                        return $months;
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'],
                                fn (Builder $query, $month): Builder => $query->whereHas('order', 
                                    fn ($q) => $q->whereYear('tanggal_order', \Carbon\Carbon::parse($month)->year)
                                        ->whereMonth('tanggal_order', \Carbon\Carbon::parse($month)->month)
                                )
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('exportExcel')
                    ->label('Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (SalesReport $record) {
                        $month = $record->order->tanggal_order->format('F-Y');
                        return Excel::download(new SalesReportsExport($record), "laporan-penjualan-{$month}.xlsx");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
    ->label('Export Excel')
    ->color('primary')
    ->icon('heroicon-o-arrow-down-tray')
    ->form([
        Forms\Components\Select::make('type')
            ->options([
                'monthly' => 'Bulanan',
                'custom' => 'Periode Kustom',
            ])
            ->default('monthly')
            ->live()
            ->label('Jenis Export'),
        
        Forms\Components\Select::make('bulan')
            ->options([
                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
                '4' => 'April', '5' => 'Mei', '6' => 'Juni',
                '7' => 'Juli', '8' => 'Agustus', '9' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ])
            ->default(now()->month)
            ->label('Bulan')
            ->visible(fn (Forms\Get $get) => $get('type') === 'monthly'),
        
        Forms\Components\Select::make('tahun')
            ->options(function() {
                $years = [];
                for ($i = 2020; $i <= now()->year; $i++) {
                    $years[$i] = $i;
                }
                return $years;
            })
            ->default(now()->year)
            ->label('Tahun')
            ->visible(fn (Forms\Get $get) => $get('type') === 'monthly'),
        
        Forms\Components\DatePicker::make('start_date')
            ->label('Dari Tanggal')
            ->default(now()->startOfMonth())
            ->visible(fn (Forms\Get $get) => $get('type') === 'custom'),
        
        Forms\Components\DatePicker::make('end_date')
            ->label('Sampai Tanggal')
            ->default(now()->endOfMonth())
            ->visible(fn (Forms\Get $get) => $get('type') === 'custom'),
    ])
    ->action(function (array $data) {
        $query = SalesReport::query()
            ->whereHas('order', fn($q) => $q->where('status', 'dibayar'))
            ->with(['order.vehicle', 'order.user']);
        
        if ($data['type'] === 'monthly') {
            $query->whereHas('order', function($q) use ($data) {
                $q->whereMonth('tanggal_order', $data['bulan'])
                  ->whereYear('tanggal_order', $data['tahun']);
            });
            
            $monthName = [
                '1' => 'Januari', '2' => 'Februari', '3' => 'Maret',
                '4' => 'April', '5' => 'Mei', '6' => 'Juni',
                '7' => 'Juli', '8' => 'Agustus', '9' => 'September',
                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
            ][$data['bulan']];
            
            $filename = "laporan-penjualan-{$monthName}-{$data['tahun']}.xlsx";
        } else {
            $query->whereHas('order', function($q) use ($data) {
                $q->whereBetween('tanggal_order', [
                    $data['start_date'],
                    $data['end_date']
                ]);
            });
            
            $start = \Carbon\Carbon::parse($data['start_date'])->format('d-m-Y');
            $end = \Carbon\Carbon::parse($data['end_date'])->format('d-m-Y');
            $filename = "laporan-penjualan-{$start}-sampai-{$end}.xlsx";
        }
        
        $reports = $query->get();
        
        return Excel::download(new SalesReportsExport($reports), $filename);
    }),
            ])

            ->defaultSort('order.tanggal_order', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReports::route('/'),
            'create' => Pages\CreateSalesReport::route('/create'),
            'edit' => Pages\EditSalesReport::route('/{record}/edit'),
        ];
    }
}