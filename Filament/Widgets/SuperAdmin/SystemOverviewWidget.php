<?php

namespace App\Filament\Widgets\SuperAdmin;

use App\Models\User;
use App\Models\Karyawan;
use App\Models\Attendance;
use App\Models\Leave;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class SystemOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    protected function getStats(): array
    {
        $totalUsers = User::count();
        $totalEmployees = Karyawan::count();
        $todayAttendance = Attendance::whereDate('created_at', today())->count();
        $pendingLeaves = Leave::where('status', 'pending')->count();

        return [
            Stat::make('Total Pengguna', $totalUsers)
                ->description('Semua pengguna sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make('Total Karyawan', $totalEmployees)
                ->description('Karyawan terdaftar')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),

            Stat::make('Kehadiran Hari Ini', $todayAttendance)
                ->description('Yang sudah absen hari ini')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),

            Stat::make('Cuti Pending', $pendingLeaves)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
