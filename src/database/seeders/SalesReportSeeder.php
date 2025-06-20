<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SalesReport;
use App\Models\Order;

class SalesReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::where('status', 'dibayar')->get();

        foreach ($orders as $order) {
            SalesReport::updateOrCreate([
                'order_id' => $order->id,
            ]);
        }
    }
}
