<?php

namespace App\Filament\Widgets\HRD;

use App\Models\Karyawan;
use App\Models\Jabatan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class EmployeeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'hrd']);
    }

    protected function getStats(): array
    {
        $totalEmployees = Karyawan::count();
        $activeEmployees = Karyawan::whereHas('user', function($query) {
            $query->whereNotNull('email_verified_at');
        })->count();
        $totalPositions = Jabatan::count();
        $newEmployeesThisMonth = Karyawan::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            Stat::make('Total Karyawan', $totalEmployees)
                ->description('Semua karyawan terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Karyawan Aktif', $activeEmployees)
                ->description('Karyawan dengan status aktif')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Total Jabatan', $totalPositions)
                ->description('Jabatan yang tersedia')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Karyawan Baru', $newEmployeesThisMonth)
                ->description('Bulan ini')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('warning'),
        ];
    }
}
