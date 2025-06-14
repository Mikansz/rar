<?php

namespace App\Filament\Widgets\Karyawan;

use App\Models\Penggajian;
use App\Models\Karyawan;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PersonalPayrollWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    public static function canView(): bool
    {
        return Auth::user()->hasRole('karyawan') || Auth::user()->hasAnyRole(['super_admin', 'cfo']);
    }

    protected function getStats(): array
    {
        $user = Auth::user();
        $karyawan = $user->karyawan;

        if (!$karyawan) {
            return [
                Stat::make('Data Karyawan', 'Tidak Ditemukan')
                    ->description('Silakan hubungi HRD')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }

        // Gaji bulan ini
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        $currentPayroll = Penggajian::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal_gaji', $currentMonth)
            ->whereYear('tanggal_gaji', $currentYear)
            ->first();

        // Gaji bulan lalu
        $lastMonth = now()->subMonth();
        $lastPayroll = Penggajian::where('karyawan_id', $karyawan->id)
            ->whereMonth('tanggal_gaji', $lastMonth->month)
            ->whereYear('tanggal_gaji', $lastMonth->year)
            ->first();

        // Total gaji tahun ini
        $yearlyTotal = Penggajian::where('karyawan_id', $karyawan->id)
            ->whereYear('tanggal_gaji', $currentYear)
            ->sum('total_gaji');

        // Gaji pokok dari jabatan
        $basicSalary = $karyawan->jabatan ? $karyawan->jabatan->gaji_pokok : 0;

        return [
            Stat::make('Gaji Bulan Ini', $currentPayroll ? 'Rp ' . number_format($currentPayroll->total_gaji, 0, ',', '.') : 'Belum Diproses')
                ->description($currentPayroll ? 'Sudah dibayar' : 'Menunggu proses')
                ->descriptionIcon($currentPayroll ? 'heroicon-m-check-circle' : 'heroicon-m-clock')
                ->color($currentPayroll ? 'success' : 'warning'),

            Stat::make('Gaji Bulan Lalu', $lastPayroll ? 'Rp ' . number_format($lastPayroll->total_gaji, 0, ',', '.') : 'Tidak Ada Data')
                ->description('Periode ' . $lastMonth->format('F Y'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),

            Stat::make('Total Gaji Tahun Ini', 'Rp ' . number_format($yearlyTotal, 0, ',', '.'))
                ->description('Akumulasi tahun ' . $currentYear)
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),

            Stat::make('Gaji Pokok', 'Rp ' . number_format($basicSalary, 0, ',', '.'))
                ->description('Sesuai jabatan: ' . ($karyawan->jabatan->nama_jabatan ?? 'Tidak ada'))
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('gray'),
        ];
    }
}
