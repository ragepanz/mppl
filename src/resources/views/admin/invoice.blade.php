<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
        .text-right { text-align: right; }
        .mt-5 { margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVOICE</h2>
        <p>No: INV/{{ $order->id }}/{{ date('Y') }}</p>
    </div>

    <table class="table">
        <tr>
            <th>Tanggal</th>
            <td>{{ $date }}</td>
            <th>Pelanggan</th>
            <td>{{ $order->user->name }}</td>
        </tr>
    </table>

    <h3 class="mt-5">Detail Pesanan:</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Kendaraan</th>
                <th>Harga</th>
                <th>Tanggal Pesan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $order->vehicle->nama }} ({{ $order->vehicle->merk }})</td>
                <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                <td>{{ $order->tanggal_order->format('d/m/Y') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-5">
        <p>Status Pembayaran: <strong>Lunas</strong></p>
        <p>Diverifikasi pada: {{ $order->payment_verified_at->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>