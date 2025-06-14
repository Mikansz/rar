<?php

namespace App\Filament\Widgets\CFO;

use App\Models\Penggajian;
use App\Models\Karyawan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class FinancialOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'cfo']);
    }

    protected function getStats(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Total penggajian bulan ini
        $totalPayrollThisMonth = Penggajian::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->sum('total_gaji');

        // Total karyawan yang sudah digaji bulan ini
        $employeesPaidThisMonth = Penggajian::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->distinct('karyawan_id')
            ->count();

        // Total karyawan aktif
        $totalActiveEmployees = Karyawan::whereHas('user', function($query) {
            $query->whereNotNull('email_verified_at');
        })->count();

        // Rata-rata gaji
        $averageSalary = $totalPayrollThisMonth > 0 && $employeesPaidThisMonth > 0 
            ? $totalPayrollThisMonth / $employeesPaidThisMonth 
            : 0;

        return [
            Stat::make('Total Penggajian Bulan Ini', 'Rp ' . number_format($totalPayrollThisMonth, 0, ',', '.'))
                ->description('Pengeluaran gaji bulan ' . now()->format('F'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),

            Stat::make('Karyawan Terbayar', $employeesPaidThisMonth . ' / ' . $totalActiveEmployees)
                ->description('Yang sudah menerima gaji')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Rata-rata Gaji', 'Rp ' . number_format($averageSalary, 0, ',', '.'))
                ->description('Per karyawan bulan ini')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Pending Payroll', $totalActiveEmployees - $employeesPaidThisMonth)
                ->description('Belum diproses')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
