<?php

namespace App\Filament\Widgets\CFO;

use App\Models\Penggajian;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PayrollStatsWidget extends ChartWidget
{
    protected static ?string $heading = 'Distribusi Gaji Berdasarkan Jabatan';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'cfo']);
    }

    protected function getData(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $payrollStats = Penggajian::join('karyawan', 'penggajian.karyawan_id', '=', 'karyawan.id')
            ->join('jabatan', 'karyawan.jabatan_id', '=', 'jabatan.id')
            ->select('jabatan.nama_jabatan', DB::raw('SUM(penggajian.total_gaji) as total'))
            ->whereMonth('penggajian.created_at', $currentMonth)
            ->whereYear('penggajian.created_at', $currentYear)
            ->groupBy('jabatan.nama_jabatan')
            ->get();

        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

        foreach ($payrollStats as $index => $stat) {
            $labels[] = $stat->nama_jabatan;
            $data[] = $stat->total;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Gaji (Rp)',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
