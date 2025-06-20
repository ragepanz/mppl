<!DOCTYPE html>
<html>
<head>
    <title>Cetak Pesanan</title>
</head>
<body>
    <h1>Pesanan #{{ $order->id }}</h1>
    <p>Tanggal Pesan: {{ \Carbon\Carbon::parse($order->tanggal_order)->translatedFormat('d F Y') }}</p>
    <p>Kendaraan: {{ $order->vehicle->nama }} ({{ $order->vehicle->merk }} - {{ $order->vehicle->tipe }})</p>
    <p>Harga: Rp {{ number_format($order->total_harga, 0, ',', '.') }}</p>
    <p>Status: {{ ucfirst($order->status) }}</p>
    <p>Catatan: {{ $order->catatan ?? '-' }}</p>

    <script>
        // Auto print
        window.print();
    </script>
</body>
</html>
    