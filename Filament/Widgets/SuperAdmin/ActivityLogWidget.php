<?php

namespace App\Filament\Widgets\SuperAdmin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Leave;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class ActivityLogWidget extends BaseWidget
{
    protected static ?string $heading = 'Kehadiran Terbaru';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('super_admin');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Use a simpler approach - just show recent attendances with user info
                Attendance::query()
                    ->with(['user'])
                    ->latest()
                    ->limit(15)
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Masuk')
                    ->time('H:i'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Keluar')
                    ->time('H:i')
                    ->placeholder('Belum keluar'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
