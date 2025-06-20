<?php

namespace App\Exports;

use App\Models\SalesReport;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use NumberFormatter;

class SalesReportsExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Collection $reports;
    protected string $month;

    public function __construct($reports)
    {
        $this->reports = $this->normalizeReports($reports);
        $this->month = $this->reports->first()->order->tanggal_order->format('F Y');
    }

    public function title(): string
    {
        return $this->month;
    }

    public function collection()
    {
        if ($this->shouldEagerLoad()) {
            $this->reports = $this->loadRelationships();
        }

        return $this->reports;
    }

    public function headings(): array
    {
        return [
            'ID Laporan',
            'ID Pesanan',
            'Pelanggan',
            'Kendaraan',
            'Total Harga (Rp)',
            'Tanggal Pesan',
            'Tanggal Laporan',
            'Bulan',
            'Keterangan'
        ];
    }

    public function map($report): array
    {
        $formatter = new NumberFormatter('id_ID', NumberFormatter::CURRENCY);
        $totalHarga = $report->order->total_harga ?? 0;
        $formattedHarga = $formatter->formatCurrency($totalHarga, 'IDR');

        return [
            $report->id,
            $report->order->id ?? '-',
            $report->order->user->name ?? '-',
            $report->order->vehicle->nama ?? '-',
            $formattedHarga,
            $report->order->tanggal_order?->format('d/m/Y') ?? '-',
            $report->created_at->format('d/m/Y H:i'),
            $report->order->tanggal_order?->format('F Y') ?? '-',
            $report->keterangan ?? '-',
        ];
    }

    protected function normalizeReports($reports): Collection
    {
        if ($reports instanceof Collection) {
            return $reports;
        }

        if ($reports instanceof SalesReport) {
            return collect([$reports]);
        }

        return collect($reports);
    }

    protected function shouldEagerLoad(): bool
    {
        return $this->reports->isNotEmpty() && $this->reports->first() instanceof SalesReport;
    }

    protected function loadRelationships(): Collection
    {
        $reportIds = $this->reports->pluck('id');

        return SalesReport::with([
                'order.vehicle',
                'order.user'
            ])
            ->whereIn('id', $reportIds)
            ->get();
    }
}