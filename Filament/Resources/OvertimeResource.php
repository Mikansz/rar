<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OvertimeResource\Pages;
use App\Models\Overtime;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class OvertimeResource extends Resource
{
    protected static ?string $model = Overtime::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Lembur';

    protected static ?string $modelLabel = 'Lembur';

    protected static ?string $pluralModelLabel = 'Lembur';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Jika bukan admin/HRD, hanya tampilkan data milik user yang login
        if (! Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd'])) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Lembur')
                    ->schema([
                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),

                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now())
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $date = Carbon::parse($state);
                                    $type = Overtime::getOvertimeType($date);
                                    $set('type', $type);
                                    $set('rate_per_hour', Overtime::getDefaultRate($type));
                                }
                            }),

                        Forms\Components\Select::make('type')
                            ->label('Jenis Lembur')
                            ->options(Overtime::getOvertimeTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('rate_per_hour', Overtime::getDefaultRate($state));
                            }),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('start_time')
                                    ->label('Waktu Mulai')
                                    ->required()
                                    ->format('H:i'),

                                Forms\Components\TimePicker::make('end_time')
                                    ->label('Waktu Selesai')
                                    ->required()
                                    ->format('H:i')
                                    ->after('start_time'),
                            ]),

                        Forms\Components\TextInput::make('hours')
                            ->label('Jumlah Jam')
                            ->numeric()
                            ->step(0.5)
                            ->minValue(0)
                            ->maxValue(24)
                            ->required()
                            ->helperText('Akan dihitung otomatis berdasarkan waktu mulai dan selesai'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Lembur')
                            ->required()
                            ->rows(3),
                    ]),

                Section::make('Informasi Pembayaran')
                    ->schema([
                        Forms\Components\TextInput::make('rate_per_hour')
                            ->label('Tarif per Jam (Rp)')
                            ->numeric()
                            ->required()
                            ->disabled(fn () => ! Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd']))
                            ->helperText('Tarif akan diatur otomatis berdasarkan jenis lembur'),

                        Forms\Components\TextInput::make('total_amount')
                            ->label('Total Pembayaran (Rp)')
                            ->numeric()
                            ->disabled()
                            ->helperText('Dihitung otomatis: jam lembur × tarif per jam'),
                    ]),

                Section::make('Status Persetujuan')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(Overtime::getStatusOptions())
                            ->default(Overtime::STATUS_PENDING)
                            ->disabled(fn () => ! Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd'])),

                        Forms\Components\Textarea::make('approval_note')
                            ->label('Catatan Persetujuan')
                            ->rows(2)
                            ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd'])),
                    ])
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Karyawan')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->formatStateUsing(fn ($state) => Overtime::getOvertimeTypes()[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'weekday' => 'primary',
                        'weekend' => 'warning',
                        'holiday' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('hours')
                    ->label('Jam')
                    ->suffix(' jam')
                    ->sortable(),

                Tables\Columns\TextColumn::make('rate_per_hour')
                    ->label('Tarif/Jam')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(Overtime::getStatusOptions()),

                SelectFilter::make('type')
                    ->label('Jenis Lembur')
                    ->options(Overtime::getOvertimeTypes()),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record && Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd']) && $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->update([
                            'status' => 'approved',
                        ]);
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn ($record) => $record && Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd']) && $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('approval_note')
                            ->label('Alasan Penolakan')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'approval_note' => $data['approval_note'],
                        ]);
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record && $record->status === 'pending'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hrd', 'Hrd'])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOvertimes::route('/'),
            'create' => Pages\CreateOvertime::route('/create'),
            'view' => Pages\ViewOvertime::route('/{record}'),
            'edit' => Pages\EditOvertime::route('/{record}/edit'),
        ];
    }
}
