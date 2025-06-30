<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentAccount;

class PaymentAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            PaymentAccount::create([
                'type' => 'bank_transfer',
                'bank_name' => 'BCA',
                'account_name' => 'PT. Capunk Mobilindo Sejahtera',
                'account_number' => '1234567890',
                'instructions' => 'Harap transfer tepat sesuai nominal dan tambahkan kode unik jika diperlukan'
            ]);
            $this->command->info('Payment account created successfully!');
        } catch (\Exception $e) {
            $this->command->error('Error creating payment account: '.$e->getMessage());
        }
    }
}
