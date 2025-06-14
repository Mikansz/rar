<?php

namespace App\Filament\Widgets\CFO;

use App\Models\Penggajian;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran Gaji 6 Bulan Terakhir';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'cfo']);
    }

    protected function getData(): array
    {
        $monthlyData = Penggajian::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total_gaji) as total')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        // Generate labels untuk 6 bulan terakhir
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            
            $labels[] = $date->format('M Y');
            
            // Cari data untuk bulan ini
            $monthData = $monthlyData->where('year', $year)->where('month', $month)->first();
            $data[] = $monthData ? $monthData->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Pengeluaran Gaji (Rp)',
                    'data' => $data,
                    'borderColor' => '#FF6384',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
