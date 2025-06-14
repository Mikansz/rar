<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CFO\FinancialOverviewWidget;
use App\Filament\Widgets\CFO\PayrollStatsWidget;
use App\Filament\Widgets\CFO\MonthlyRevenueChart;
use App\Filament\Widgets\HRD\EmployeeStatsWidget;
use App\Filament\Widgets\HRD\AttendanceOverviewWidget;
use App\Filament\Widgets\HRD\LeaveRequestsWidget;
use App\Filament\Widgets\Karyawan\PersonalAttendanceWidget;
use App\Filament\Widgets\Karyawan\PersonalLeaveWidget;
use App\Filament\Widgets\Karyawan\PersonalPayrollWidget;
use App\Filament\Widgets\SuperAdmin\SystemOverviewWidget;
use App\Filament\Widgets\SuperAdmin\UserStatsWidget;
use App\Filament\Widgets\SuperAdmin\ActivityLogWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    
    protected static string $view = 'filament.pages.dashboard';

    public function getWidgets(): array
    {
        $user = Auth::user();
        $widgets = [];

        // Widget untuk Super Admin
        if ($user->hasRole('super_admin')) {
            $widgets = [
                SystemOverviewWidget::class,
                UserStatsWidget::class,
                ActivityLogWidget::class,
                // Tambahan widget admin lainnya
                EmployeeStatsWidget::class,
                AttendanceOverviewWidget::class,
                FinancialOverviewWidget::class,
            ];
        }
        // Widget untuk HRD
        elseif ($user->hasRole('hrd')) {
            $widgets = [
                EmployeeStatsWidget::class,
                AttendanceOverviewWidget::class,
                LeaveRequestsWidget::class,
            ];
        }
        // Widget untuk CFO
        elseif ($user->hasRole('cfo')) {
            $widgets = [
                FinancialOverviewWidget::class,
                PayrollStatsWidget::class,
                MonthlyRevenueChart::class,
            ];
        }
        // Widget untuk Karyawan
        elseif ($user->hasRole('karyawan')) {
            $widgets = [
                PersonalAttendanceWidget::class,
                PersonalLeaveWidget::class,
                PersonalPayrollWidget::class,
            ];
        }
        // Default widgets jika tidak ada role yang cocok
        else {
            $widgets = [
                PersonalAttendanceWidget::class,
            ];
        }

        return $widgets;
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function getTitle(): string
    {
        $user = Auth::user();
        $role = $user->getPrimaryRole();
        
        $titles = [
            'super_admin' => 'Dashboard Administrator',
            'hrd' => 'Dashboard HRD',
            'cfo' => 'Dashboard CFO',
            'karyawan' => 'Dashboard Karyawan',
        ];

        return $titles[$role] ?? 'Dashboard';
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        $role = $user->getPrimaryRole();
        
        $subheadings = [
            'super_admin' => 'Kelola seluruh sistem dan monitor aktivitas',
            'hrd' => 'Kelola karyawan, kehadiran, dan cuti',
            'cfo' => 'Monitor keuangan dan penggajian',
            'karyawan' => 'Lihat informasi personal Anda',
        ];

        return $subheadings[$role] ?? 'Selamat datang di sistem';
    }
}
