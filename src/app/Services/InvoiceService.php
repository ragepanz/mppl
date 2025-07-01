<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    public function generatePdf(Order $order)
    {
        $pdf = Pdf::loadView('admin.invoice', [
            'order' => $order,
            'date' => now()->format('d/m/Y'),
        ]);

        return $pdf->stream('invoice-'.$order->id.'.pdf');
    }
}