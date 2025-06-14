<?php

namespace App\Filament\Widgets\HRD;

use App\Models\Attendance;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceOverviewWidget extends ChartWidget
{
    protected static ?string $heading = 'Kehadiran 7 Hari Terakhir';
    protected static ?int $sort = 2;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'hrd']);
    }

    protected function getData(): array
    {
        $attendanceData = Attendance::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        // Generate labels untuk 7 hari terakhir
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = now()->subDays($i)->format('d/m');
            
            // Cari data untuk tanggal ini
            $attendance = $attendanceData->firstWhere('date', $date);
            $data[] = $attendance ? $attendance->total : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Kehadiran',
                    'data' => $data,
                    'borderColor' => '#36A2EB',
                    'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
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
