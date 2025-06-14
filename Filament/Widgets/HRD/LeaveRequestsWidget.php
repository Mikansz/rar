<?php

namespace App\Filament\Widgets\HRD;

use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class LeaveRequestsWidget extends BaseWidget
{
    protected static ?string $heading = 'Permintaan Cuti Terbaru';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasAnyRole(['super_admin', 'hrd']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Leave::query()
                    ->with(['user'])
                    ->where('status', 'pending')
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Jenis Cuti')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'cuti_tahunan' => 'success',
                        'cuti_sakit' => 'danger',
                        'izin' => 'warning',
                        default => 'gray',
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
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-m-check')
                    ->color('success')
                    ->action(function (Leave $record) {
                        $record->update(['status' => 'approved']);
                    })
                    ->visible(fn (Leave $record) => $record->status === 'pending'),
                Tables\Actions\Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-m-x-mark')
                    ->color('danger')
                    ->action(function (Leave $record) {
                        $record->update(['status' => 'rejected']);
                    })
                    ->visible(fn (Leave $record) => $record->status === 'pending'),
            ])
            ->paginated(false);
    }
}
