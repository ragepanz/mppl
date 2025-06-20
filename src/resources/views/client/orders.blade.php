    <!-- resources/views/client/orders.blade.php -->
@extends('layouts.app')

@section('content')
    <h2>Riwayat Pesanan</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kendaraan</th>
                <th>Status</th>
                <th>Total</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $order->vehicle->nama }} - {{ $order->vehicle->tahun }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                    <td>
                        <!-- Tombol tampil invoice -->
                        <a href="{{ route('client.invoice.show', $order->id) }}" class="btn btn-primary">Lihat Invoice</a>

                        <!-- Tombol download PDF -->
                        <a href="{{ route('client.invoice.download', $order->id) }}" class="btn btn-secondary">Download PDF</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
