<?php

namespace App\Filament\Client\Resources\OrderResource\Pages;

use App\Filament\Client\Resources\OrderResource;
use App\Models\Order;
use App\Models\PaymentAccount;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PayOrder extends Page
{
    protected static string $resource = OrderResource::class;
    protected static string $view = 'filament.client.resources.order-resource.pages.pay-order';

    public Order $record;
    public ?array $data = [];
    public ?array $paymentAccountInfo = [];

    public function mount(Order $record): void
    {
        $this->record = $record;

        if (!$this->isOrderPayable()) {
            Notification::make()
                ->title('Order Tidak Dapat Dibayar')
                ->body('Status order saat ini: ' . $this->record->status)
                ->danger()
                ->persistent()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }

        if (PaymentAccount::count() === 0) {
            Notification::make()
                ->title('Metode Pembayaran Tidak Tersedia')
                ->body('Silahkan hubungi admin untuk menambahkan metode pembayaran')
                ->danger()
                ->persistent()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
            return;
        }

        $initialPaymentMethod = PaymentAccount::first()->type;
        $this->paymentAccountInfo = $this->getPaymentAccountInfo($initialPaymentMethod);

        $this->form->fill([
            'amount' => $this->record->total_harga,
            'payment_method' => $initialPaymentMethod,
            'bank_name' => $this->paymentAccountInfo['bank_name'] ?? null,
            'account_name' => $this->paymentAccountInfo['account_name'] ?? null,
            'account_number' => $this->paymentAccountInfo['account_number'] ?? null,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pembayaran')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->options($this->getPaymentMethodOptions())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state) {
                                $this->paymentAccountInfo = $this->getPaymentAccountInfo($state);
                                $this->form->fill([
                                    'bank_name' => $this->paymentAccountInfo['bank_name'] ?? null,
                                    'account_name' => $this->paymentAccountInfo['account_name'] ?? null,
                                    'account_number' => $this->paymentAccountInfo['account_number'] ?? null,
                                ]);
                            })
                            ->searchable()
                            ->native(false),

                        Section::make('Rekening Tujuan')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank/Provider')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('account_name')
                                    ->label('Nama Pemilik Rekening')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('account_number')
                                    ->label('Nomor Rekening/Akun')
                                    ->disabled()
                                    ->dehydrated(),

                                Forms\Components\Placeholder::make('instructions')
                                    ->content(fn() => $this->paymentAccountInfo['instructions'] ?? '')
                                    ->hidden(fn() => empty($this->paymentAccountInfo['instructions']))
                            ])
                            ->hidden(fn() => empty($this->data['payment_method']))
                            ->columnSpanFull(),

                        TextInput::make('amount')
                            ->label('Jumlah Pembayaran')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->default($this->record->total_harga)
                            ->minValue(1)
                            ->maxValue(fn() => $this->record->total_harga * 1.1)
                            ->rules([
                                function () {
                                    return function (string $attribute, $value, $fail) {
                                        if ($value < $this->record->total_harga) {
                                            $fail('Jumlah pembayaran kurang dari total tagihan');
                                        }
                                    };
                                }
                            ]),

                        TextInput::make('sender_name')
                            ->label('Nama Pengirim')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('sender_account')
                            ->label('Nomor Rekening/E-Wallet Pengirim')
                            ->required()
                            ->maxLength(50),

                        FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->directory('payment-proofs')
                            ->required()
                            ->maxSize(2048)
                            ->downloadable()
                            ->openable()
                            ->helperText('Format: JPG/PNG (max 2MB)')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->columnSpanFull()
                    ])
                    ->columns(2)
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('submit')
                ->label('Kirim Pembayaran')
                ->submit('submitPayment')
                ->color('primary')
                ->icon('heroicon-o-credit-card')
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index'))
        ];
    }

    public function submitPayment(): void
    {
        try {
            $data = $this->form->getState();

            if (empty($data['payment_method'])) {
                throw new \Exception('Silakan pilih metode pembayaran terlebih dahulu');
            }

            if (empty($data['payment_proof'])) {
                throw new \Exception('Bukti pembayaran harus diupload');
            }

            $paymentDetails = $this->processPayment($data);

            $this->updateOrderAfterPayment($paymentDetails);

            Notification::make()
                ->title('Pembayaran Berhasil')
                ->body('Pembayaran sebesar Rp ' . number_format($this->record->total_harga, 0, ',', '.') . ' telah diterima dan sedang menunggu verifikasi admin')
                ->success()
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal Melakukan Pembayaran')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            report($e);
        }
    }

    protected function isOrderPayable(): bool
    {
        $payableStatuses = ['pending'];
        return in_array($this->record->status, $payableStatuses);
    }

    protected function getPaymentMethodOptions(): array
    {
        return PaymentAccount::all()->mapWithKeys(function ($account) {
            return [$account->type => $this->getPaymentMethodLabel($account)];
        })->toArray();
    }

    protected function getPaymentAccountInfo(string $paymentMethod): array
    {
        $account = PaymentAccount::where('type', $paymentMethod)->first();

        return $account ? [
            'bank_name' => $account->bank_name,
            'account_name' => $account->account_name,
            'account_number' => $account->account_number,
            'instructions' => $account->instructions ?? null
        ] : [];
    }

    protected function processPayment(array $data): array
    {
        $proofPath = $data['payment_proof'] instanceof TemporaryUploadedFile
            ? $data['payment_proof']->store('payment-proofs', 'public')
            : $data['payment_proof'];

        return [
            'method' => $data['payment_method'],
            'amount' => (float) $data['amount'],
            'proof_path' => Storage::url($proofPath),
            'sender_name' => $data['sender_name'],
            'sender_account' => $data['sender_account'],
            'destination' => [
                'bank' => $data['bank_name'],
                'account_name' => $data['account_name'],
                'account_number' => $data['account_number']
            ],
            'paid_at' => now()->toDateTimeString()
        ];
    }

    protected function updateOrderAfterPayment(array $paymentDetails): void
    {
        $this->record->update([
            'status' => 'proses',
            'payment_method' => $paymentDetails['method'],
            'payment_amount' => $paymentDetails['amount'],
            'payment_proof' => $paymentDetails['proof_path'],
            'payment_date' => $paymentDetails['paid_at'],
            'payment_details' => json_encode([
                'sender_name' => $paymentDetails['sender_name'],
                'sender_account' => $paymentDetails['sender_account'],
                'destination' => $paymentDetails['destination']
            ])
        ]);
    }

    protected function getPaymentMethodLabel(PaymentAccount $account): string
    {
        $labels = [
            'bank_transfer' => 'Transfer Bank - ' . $account->bank_name,
        ];

        return $labels[$account->type] ?? $account->type;
    }

    public function getBreadcrumbs(): array
    {
        return [
            static::getResource()::getUrl() => 'Pesanan Saya',
            'Pembayaran',
        ];
    }
}
