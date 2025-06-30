<?php

namespace App\Filament\Admin\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class PaymentRelationManager extends RelationManager
{
    protected static string $relationship = 'payment';

    protected static ?string $title = 'Payment Confirmation';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'proses' => 'Proses',
                        'dibayar' => 'Dibayar',
                    ])
                    ->required(),
                    
                Forms\Components\Textarea::make('payment_notes')
                    ->label('Admin Notes')
                    ->placeholder('Enter any notes for the customer')
                    ->maxLength(500),
                    
                Forms\Components\DateTimePicker::make('payment_verified_at')
                    ->label('Verification Date'),
                    
                Forms\Components\DateTimePicker::make('payment_rejected_at')
                    ->label('Rejection Date'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('status')
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'proses' => 'warning',
                        'dibayar' => 'success',
                    }),
                    
                Tables\Columns\TextColumn::make('payment_verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_rejected_at')
                    ->label('Rejected At')
                    ->dateTime()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('payment_notes')
                    ->label('Admin Notes')
                    ->limit(50),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'proses' => 'Proses',
                                'dibayar' => 'Dibayar',
                            ])
                            ->required(),
                            
                        Forms\Components\Textarea::make('payment_notes')
                            ->label('Admin Notes'),
                            
                        Forms\Components\DateTimePicker::make('payment_verified_at')
                            ->label('Verification Date'),
                            
                        Forms\Components\DateTimePicker::make('payment_rejected_at')
                            ->label('Rejection Date'),
                    ])
                    ->beforeFormFilled(function (Model $record) {
                        // Auto-fill verification date when status is changed to 'dibayar'
                        if ($record->status === 'dibayar' && !$record->payment_verified_at) {
                            $record->payment_verified_at = now();
                        }
                        
                        // Auto-fill rejection date when status is changed to 'pending' or 'proses'
                        if (in_array($record->status, ['pending', 'proses']) && !$record->payment_rejected_at) {
                            $record->payment_rejected_at = now();
                        }
                    })
                    ->after(function (Model $record) {
                        // Send notification to customer
                        if ($record->status === 'dibayar') {
                            Notification::make()
                                ->title('Pembayaran Diverifikasi')
                                ->body('Pembayaran Anda telah diverifikasi oleh admin.')
                                ->sendToDatabase($record->user);
                        } elseif ($record->status === 'proses') {
                            Notification::make()
                                ->title('Pembayaran Diproses')
                                ->body('Pembayaran Anda sedang diproses oleh admin.')
                                ->sendToDatabase($record->user);
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Payment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'proses' => 'warning',
                                'dibayar' => 'success',
                            }),
                            
                        Infolists\Components\TextEntry::make('payment_verified_at')
                            ->label('Verified At')
                            ->dateTime(),
                            
                        Infolists\Components\TextEntry::make('payment_rejected_at')
                            ->label('Rejected At')
                            ->dateTime(),
                            
                        Infolists\Components\TextEntry::make('payment_notes')
                            ->label('Admin Notes')
                            ->columnSpanFull(),
                    ])->columns(2),
                    
                Infolists\Components\Section::make('Payment Proof')
                    ->schema([
                        Infolists\Components\ImageEntry::make('payment_proof')
                            ->height(300),
                    ]),
            ]);
    }
}