<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SickLeaveResource\Pages;
use App\Models\Leave;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SickLeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationLabel = 'Cuti Sakit';

    protected static ?string $modelLabel = 'Cuti Sakit';

    protected static ?string $pluralModelLabel = 'Cuti Sakit';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->where('leave_type', Leave::CUTI_SAKIT);

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
                Section::make('Informasi Cuti Sakit')
                    ->schema([
                        Forms\Components\Hidden::make('leave_type')
                            ->default(Leave::CUTI_SAKIT),

                        Forms\Components\Hidden::make('user_id')
                            ->default(Auth::id()),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->default(now())
                            ->afterOrEqual('start_date'),

                        Forms\Components\Textarea::make('symptoms')
                            ->label('Gejala/Keluhan')
                            ->required()
                            ->rows(3)
                            ->helperText('Jelaskan gejala atau keluhan yang dirasakan'),

                        Forms\Components\Textarea::make('reason')
                            ->label('Keterangan Tambahan')
                            ->rows(2),
                    ]),

                Section::make('Informasi Medis')
                    ->schema([
                        Forms\Components\TextInput::make('doctor_name')
                            ->label('Nama Dokter')
                            ->helperText('Opsional: Nama dokter yang memeriksa'),

                        Forms\Components\TextInput::make('hospital_clinic')
                            ->label('Rumah Sakit/Klinik')
                            ->helperText('Opsional: Nama tempat berobat'),

                        Forms\Components\FileUpload::make('sick_certificate')
                            ->label('Surat Keterangan Sakit')
                            ->disk('public')
                            ->directory('sick-certificates')
                            ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                            ->maxSize(2048)
                            ->helperText('Upload surat keterangan sakit (PDF, JPG, PNG, max 2MB)')
                            ->downloadable()
                            ->openable()
                            ->previewable(false),
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

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('symptoms')
                    ->label('Gejala')
                    ->limit(30),

                Tables\Columns\TextColumn::make('doctor_name')
                    ->label('Dokter')
                    ->placeholder('Tidak ada'),

                Tables\Columns\IconColumn::make('sick_certificate')
                    ->label('Surat Sakit')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-minus')
                    ->getStateUsing(fn ($record) => $record && ! empty($record->sick_certificate)),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Diajukan')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('has_certificate')
                    ->label('Ada Surat Sakit')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('sick_certificate')),

                Tables\Filters\Filter::make('no_certificate')
                    ->label('Tanpa Surat Sakit')
                    ->query(fn (Builder $query): Builder => $query->whereNull('sick_certificate')),
            ])
            ->actions([
                Action::make('download_certificate')
                    ->label('Unduh Surat')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->visible(fn ($record) => $record && ! empty($record->sick_certificate))
                    ->url(fn ($record) => $record ? route('sick-certificate.download', $record) : '#')
                    ->openUrlInNewTab(),

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
            'index' => Pages\ListSickLeaves::route('/'),
            'create' => Pages\CreateSickLeave::route('/create'),
            'view' => Pages\ViewSickLeave::route('/{record}'),
            'edit' => Pages\EditSickLeave::route('/{record}/edit'),
        ];
    }
}
