<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionRequestResource\Pages;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class PermissionRequestResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationLabel = 'Izin';

    protected static ?string $modelLabel = 'Izin';

    protected static ?string $pluralModelLabel = 'Izin';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('leave_type', Leave::IZIN);

        // Jika bukan admin/HRD, hanya tampilkan data milik user yang login
        if (! Auth::user()->hasAnyRole(['super_admin', 'hrd'])) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Izin')
                    ->schema([
                        Forms\Components\Hidden::make('leave_type')
                            ->default(Leave::IZIN),

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),

                        Forms\Components\Select::make('permission_type')
                            ->label('Jenis Izin')
                            ->options(Leave::getPermissionTypes())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset waktu jika bukan izin keluar kantor sementara
                                if ($state !== Leave::IZIN_KELUAR_KANTOR) {
                                    $set('permission_start_time', null);
                                    $set('permission_end_time', null);
                                }
                            }),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->default(now())
                            ->afterOrEqual('start_date'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('permission_start_time')
                                    ->label('Waktu Mulai')
                                    ->format('H:i')
                                    ->visible(fn ($get) => $get('permission_type') === Leave::IZIN_KELUAR_KANTOR),

                                Forms\Components\TimePicker::make('permission_end_time')
                                    ->label('Waktu Selesai')
                                    ->format('H:i')
                                    ->visible(fn ($get) => $get('permission_type') === Leave::IZIN_KELUAR_KANTOR)
                                    ->after('permission_start_time'),
                            ]),

                        Forms\Components\Toggle::make('is_emergency')
                            ->label('Izin Darurat/Mendadak')
                            ->helperText('Centang jika ini adalah izin darurat yang tidak bisa diajukan sebelumnya'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan')
                            ->required()
                            ->rows(3),
                    ]),

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

                Tables\Columns\TextColumn::make('permission_type')
                    ->label('Jenis Izin')
                    ->formatStateUsing(fn ($state) => Leave::getPermissionTypes()[$state] ?? $state),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('permission_duration')
                    ->label('Durasi')
                    ->visible(fn ($record) => $record && $record->permission_type === Leave::IZIN_KELUAR_KANTOR),

                Tables\Columns\IconColumn::make('is_emergency')
                    ->label('Darurat')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('permission_type')
                    ->label('Jenis Izin')
                    ->options(Leave::getPermissionTypes()),

                SelectFilter::make('is_emergency')
                    ->label('Izin Darurat')
                    ->options([
                        '1' => 'Ya',
                        '0' => 'Tidak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()->hasAnyRole(['super_admin', 'hrd'])),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissionRequests::route('/'),
            'create' => Pages\CreatePermissionRequest::route('/create'),
            'view' => Pages\ViewPermissionRequest::route('/{record}'),
            'edit' => Pages\EditPermissionRequest::route('/{record}/edit'),
        ];
    }
}
