<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    /**
     * Tampilkan halaman invoice (HTML) di browser.
     */
    public function show(Order $order)
    {
        $user = Auth::user();
    
        if (!$user->hasRole('super_admin')) {
            abort(403, 'Anda tidak memiliki akses untuk mencetak invoice ini.');
        }
    
        $pdf = Pdf::loadView('client.invoice-pdf', compact('order'));
    
        return $pdf->download('invoice-pesanan-' . $order->id . '.pdf');
    }
    

    /**
     * Download invoice sebagai file PDF.
     */
    public function download(Order $order)
    {
        // Validasi: hanya pemilik pesanan yang bisa mengunduh
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        // Render PDF menggunakan DomPDF
        $pdf = Pdf::loadView('client.invoice-pdf', compact('order'));

        return $pdf->download('invoice-pesanan-' . $order->id . '.pdf');
    }
}
