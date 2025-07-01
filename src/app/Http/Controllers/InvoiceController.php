<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\InvoiceService;

class InvoiceController extends Controller
{
    public function download(Order $order)
    {
        if ($order->status !== 'dibayar') {
            abort(403, 'Invoice hanya tersedia untuk pesanan yang sudah dibayar');
        }

        return (new InvoiceService())->generatePdf($order);
    }
}