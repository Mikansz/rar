<?php

namespace App\Filament\Widgets\Karyawan;

use App\Models\Attendance;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PersonalAttendanceWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->hasRole('karyawan') || Auth::user()->hasAnyRole(['super_admin', 'hrd']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // Kehadiran bulan ini
        $attendanceThisMonth = Attendance::where('user_id', $user->id)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        // Kehadiran hari ini
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();

        // Total hari kerja dalam bulan (asumsi 22 hari kerja)
        $workingDaysThisMonth = 22;

        // Persentase kehadiran
        $attendancePercentage = $workingDaysThisMonth > 0 
            ? round(($attendanceThisMonth / $workingDaysThisMonth) * 100, 1)
            : 0;

        // Kehadiran minggu ini
        $attendanceThisWeek = Attendance::where('user_id', $user->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return [
            Stat::make('Kehadiran Bulan Ini', $attendanceThisMonth . ' / ' . $workingDaysThisMonth)
                ->description($attendancePercentage . '% dari target')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color($attendancePercentage >= 90 ? 'success' : ($attendancePercentage >= 75 ? 'warning' : 'danger')),

            Stat::make('Status Hari Ini', $todayAttendance ? 'Sudah Absen' : 'Belum Absen')
                ->description($todayAttendance ? 'Terima kasih!' : 'Jangan lupa absen')
                ->descriptionIcon($todayAttendance ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($todayAttendance ? 'success' : 'warning'),

            Stat::make('Kehadiran Minggu Ini', $attendanceThisWeek . ' / 5')
                ->description('Hari kerja minggu ini')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),

            Stat::make('Rata-rata Kehadiran', $attendancePercentage . '%')
                ->description('Performa kehadiran Anda')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color($attendancePercentage >= 90 ? 'success' : ($attendancePercentage >= 75 ? 'warning' : 'danger')),
        ];
    }
}
