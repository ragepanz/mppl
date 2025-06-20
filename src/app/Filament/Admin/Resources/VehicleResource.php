<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\VehicleResource\Pages;
use App\Filament\Admin\Resources\VehicleResource\RelationManagers;
use App\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\ImageColumn;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $modelLabel = 'Kendaraan';
    protected static ?string $navigationLabel = 'Manajemen Kendaraan';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Kendaraan')
                    ->description('Detail informasi kendaraan')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                            
                        Forms\Components\TextInput::make('merk')
                            ->required()
                            ->maxLength(255),
                            
                        Forms\Components\TextInput::make('tipe')
                            ->required()
                            ->maxLength(255),
                            
                        Select::make('tahun')
                            ->required()
                            ->options(
                                collect(range(1990, date('Y') + 2))
                                    ->mapWithKeys(fn ($year) => [$year => $year])
                            ),
                            
                        Forms\Components\TextInput::make('harga')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                           // ->thousandsSeparator('.')
                            ->inputMode('decimal'),
                            
                        Forms\Components\TextInput::make('stok')
                            ->required()
                            ->numeric()
                            ->minValue(0),
                            
                        FileUpload::make('foto')
                            ->image()
                            ->directory('vehicles')
                            ->preserveFilenames()
                            ->imageEditor()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->defaultImageUrl(url('/images/default-vehicle.png')),
                    
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Vehicle $record) => $record->merk),
                    
                Tables\Columns\TextColumn::make('tipe')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('tahun')
                    ->sortable()
                    ->alignCenter(),
                    
                Tables\Columns\TextColumn::make('harga')
                    ->numeric()
                    ->sortable()
                    ->money('IDR')
                    ->alignEnd(),
                    
                Tables\Columns\TextColumn::make('stok')
                    ->numeric()
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (Vehicle $record) => $record->stok < 5 ? 'danger' : 'success')
                    ->weight(fn (Vehicle $record) => $record->stok < 5 ? 'bold' : null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->options(
                        collect(range(1990, date('Y')))
                            ->mapWithKeys(fn ($year) => [$year => $year])
                            ->sortDesc()
                            ->toArray()
                    ),
                    
                Tables\Filters\Filter::make('stok_rendah')
                    ->label('Stok Rendah')
                    ->query(fn (Builder $query): Builder => $query->where('stok', '<', 5)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->iconButton(),
                Tables\Actions\DeleteAction::make()
                    ->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->defaultSort('nama', 'asc')
            ->striped();
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\TransactionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit' => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    
    public static function getNavigationBadgeColor(): string|array|null
    {
        return static::getModel()::count() < 10 ? 'warning' : 'primary';
    }
}