<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttendanceResource\Pages;
use App\Models\Attendance;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendanceResource extends Resource
{
    protected static ?string $model = Attendance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';

    protected static ?string $modelLabel = 'Absensi';

    protected static ?string $pluralModelLabel = 'Absensi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengguna')
                    ->schema([
                        Forms\Components\Select::make('user')
                            ->label('Pengguna')
                            ->relationship('user', 'name')
                            ->disabled()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Jadwal')
                    ->schema([
                        Forms\Components\TextInput::make('schedule_latitude')
                            ->label('Garis Lintang Jadwal')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('schedule_longitude')
                            ->label('Garis Bujur Jadwal')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('schedule_start_time')
                            ->label('Waktu Mulai Jadwal')
                            ->required(),
                        Forms\Components\TextInput::make('schedule_end_time')
                            ->label('Waktu Selesai Jadwal')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Absensi Masuk')
                    ->schema([
                        Forms\Components\TextInput::make('start_latitude')
                            ->label('Garis Lintang Datang')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('start_longitude')
                            ->label('Garis Bujur Datang')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('start_time')
                            ->label('Waktu Datang')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Absensi Keluar')
                    ->schema([
                        Forms\Components\TextInput::make('end_latitude')
                            ->label('Garis Lintang Pulang')
                            ->numeric(),
                        Forms\Components\TextInput::make('end_longitude')
                            ->label('Garis Bujur Pulang')
                            ->numeric(),
                        Forms\Components\TextInput::make('end_time')
                            ->label('Waktu Pulang')
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $userId = Auth::user()->id;
                $is_super_admin = Auth::user()->hasRole('super_admin');

                if (! $is_super_admin) {
                    $query->where('attendances.user_id', Auth::user()->id);
                }

            })
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->date()
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('is_late')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->isLate() && $record->isEarlyLeave()) {
                            return 'Terlambat & Pulang Duluan';
                        } elseif ($record->isLate()) {
                            return 'Terlambat';
                        } elseif ($record->isEarlyLeave()) {
                            return 'Pulang Duluan';
                        } else {
                            return 'Tepat Waktu';
                        }
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Tepat Waktu' => 'success',
                        'Terlambat' => 'danger',
                        'Pulang Duluan' => 'warning',
                        'Terlambat & Pulang Duluan' => 'danger',
                    })
                    ->description(fn (Attendance $record): string => 'Durasi : '.$record->workDuration()),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Waktu Datang'),
                Tables\Columns\TextColumn::make('end_time')
                    ->label('Waktu Pulang'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('bulan')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->placeholder('Pilih Bulan'),
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options(function () {
                                $currentYear = date('Y');
                                $years = [];
                                for ($i = $currentYear - 5; $i <= $currentYear + 1; $i++) {
                                    $years[$i] = $i;
                                }

                                return $years;
                            })
                            ->default(date('Y'))
                            ->placeholder('Pilih Tahun'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['bulan'],
                                fn (Builder $query, $bulan): Builder => $query->whereMonth('created_at', $bulan),
                            )
                            ->when(
                                $data['tahun'],
                                fn (Builder $query, $tahun): Builder => $query->whereYear('created_at', $tahun),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['bulan'] ?? null) {
                            $bulanNames = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                            ];
                            $indicators[] = 'Bulan: '.$bulanNames[$data['bulan']];
                        }

                        if ($data['tahun'] ?? null) {
                            $indicators[] = 'Tahun: '.$data['tahun'];
                        }

                        return $indicators;
                    }),

                Tables\Filters\Filter::make('hari_ini')
                    ->label('Hari Ini')
                    ->query(fn (Builder $query): Builder => $query->whereDate('created_at', today()))
                    ->toggle(),

                Tables\Filters\Filter::make('minggu_ini')
                    ->label('Minggu Ini')
                    ->query(fn (Builder $query): Builder => $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek(),
                    ]))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttendances::route('/'),
            'create' => Pages\CreateAttendance::route('/create'),
            'edit' => Pages\EditAttendance::route('/{record}/edit'),
        ];
    }
}
