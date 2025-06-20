<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #004080;
        }

        .details {
            margin-bottom: 20px;
        }

        .details p {
            margin: 4px 0;
            line-height: 1.5;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .table th {
            background-color: #004080;
            color: white;
            padding: 8px;
            text-align: left;
        }

        .table td {
            border: 1px solid #999;
            padding: 8px;
        }

        .footer {
            margin-top: 30px;
            font-size: 11px;
        }

        .signature-section {
            margin-top: 50px;
            width: 100%;
        }

        .signature-table {
            width: 100%;
            border: none;
        }

        .signature-table td {
            vertical-align: top;
            text-align: center;
            padding-top: 30px;
        }

        .signature-line {
            margin-top: 50px;
            display: block;
            border-top: 1px solid #000;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .label {
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h2>Invoice Pemesanan Kendaraan</h2>

    <div class="details">
        <p><strong>No. Pesanan:</strong> #{{ $order->id }}</p>
        <p><strong>Tanggal Order:</strong> {{ $order->tanggal_order->format('d M Y') }}</p>
        <p><strong>Nama Pelanggan:</strong> {{ $order->user->name }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Nama Kendaraan</th>
                <th>Merk</th>
                <th>Tipe</th>
                <th>Tahun</th>
                <th>Harga</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $order->vehicle->nama }}</td>
                <td>{{ $order->vehicle->merk }}</td>
                <td>{{ $order->vehicle->tipe }}</td>
                <td>{{ $order->vehicle->tahun }}</td>
                <td>Rp{{ number_format($order->total_harga, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Status Pembayaran:</strong> {{ ucfirst($order->status) }}</p>
        <p>Terima kasih telah melakukan pemesanan di Dealer Kami.</p>
    </div>

    <div class="signature-section">
        <table class="signature-table">
            <tr>
                <td>
                    <p>Hormat Kami,</p>
                    <span class="signature-line"></span>
                    <p class="label">Admin Dealer</p>
                </td>
                <td>
                    <p>Pemesan,</p>
                    <span class="signature-line"></span>
                    <p class="label">{{ $order->user->name }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
