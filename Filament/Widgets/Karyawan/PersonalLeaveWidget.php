<?php

namespace App\Filament\Widgets\Karyawan;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class PersonalLeaveWidget extends BaseWidget
{
    protected static ?string $heading = 'Riwayat Cuti & Izin Saya';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('karyawan') || Auth::user()->hasAnyRole(['super_admin', 'hrd']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cuti_tahunan' => 'success',
                        'cuti_sakit' => 'danger',
                        'izin' => 'warning',
                        'cuti_melahirkan' => 'info',
                        'cuti_penting' => 'primary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'cuti_tahunan' => 'Cuti Tahunan',
                        'cuti_sakit' => 'Cuti Sakit',
                        'izin' => 'Izin',
                        'cuti_melahirkan' => 'Cuti Melahirkan',
                        'cuti_penting' => 'Cuti Penting',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date('d/m/Y'),
                Tables\Columns\TextColumn::make('days_requested')
                    ->label('Durasi')
                    ->suffix(' hari'),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->limit(30),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        'pending' => 'Menunggu',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->date('d/m/Y'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
            ])
            ->emptyStateHeading('Belum ada riwayat cuti')
            ->emptyStateDescription('Anda belum pernah mengajukan cuti atau izin.')
            ->paginated(false);
    }
}
