<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 40px;
            color: #333;
        }
        h1, h2 { margin-bottom: 0; }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }
        .info {
            margin-bottom: 20px;
        }
        .info table { width: 100%; }
        .info td { padding: 5px; vertical-align: top; }
        .summary {
            border-top: 1px solid #ccc;
            margin-top: 30px;
            padding-top: 10px;
            text-align: right;
        }
        .label { font-weight: bold; }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 6px;
            color: #fff;
            font-size: 0.9em;
        }
        .status.pending { background-color: #f59e0b; }
        .status.proses { background-color: #3b82f6; }
        .status.dibayar { background-color: #10b981; }
    </style>
</head>
<body>

    <div class="header">
        <h1>INVOICE</h1>
        <p><strong>Invoice #: </strong>{{ $order->id }}</p>
        <p><strong>Tanggal Pesan: </strong>{{ \Carbon\Carbon::parse($order->tanggal_order)->format('d M Y') }}</p>
    </div>

    <div class="info">
        <table>
            <tr>
                <td class="label">Nama Pelanggan:</td>
                <td>{{ $order->user->name }}</td>
            </tr>
            <tr>
                <td class="label">Email Pelanggan:</td>
                <td>{{ $order->user->email }}</td>
            </tr>
            <tr>
                <td class="label">Kendaraan:</td>
                <td>{{ $order->vehicle->nama }} ({{ $order->vehicle->merk }})</td>
            </tr>
            <tr>
                <td class="label">Harga Kendaraan:</td>
                <td>Rp {{ number_format($order->vehicle->harga, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Status:</td>
                <td><span class="status {{ $order->status }}">{{ ucfirst($order->status) }}</span></td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <h2>Total: Rp {{ number_format($order->total_harga, 0, ',', '.') }}</h2>
    </div>

</body>
</html>
