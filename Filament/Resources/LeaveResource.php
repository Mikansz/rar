<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeaveResource\Pages;
use App\Models\Leave;
use Auth;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaveResource extends Resource
{
    protected static ?string $model = Leave::class;

    protected static ?string $navigationIcon = 'heroicon-m-minus-circle';

    protected static ?string $navigationGroup = 'Manajemen Kehadiran';

    protected static ?string $modelLabel = 'Cuti';

    protected static ?string $pluralModelLabel = 'Cuti';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        $schema = [
            Forms\Components\Section::make('Detail')
                ->schema([
                    Forms\Components\Select::make('leave_type')
                        ->label('Jenis Cuti')
                        ->options(Leave::getLeaveTypes())
                        ->default(Leave::CUTI_TAHUNAN)
                        ->required(),
                    Forms\Components\DatePicker::make('start_date')
                        ->label('Tanggal Mulai')
                        ->required(),
                    Forms\Components\DatePicker::make('end_date')
                        ->label('Tanggal Selesai')
                        ->required(),
                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan')
                        ->columnSpanFull(),
                ]),
        ];

        if (Auth::user()->hasRole(['super_admin', 'hrd'])) {
            $schema[] = Forms\Components\Section::make('Persetujuan')
                ->schema([
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                        ])
                        ->required()
                        ->label('Status'),
                    Forms\Components\Textarea::make('note')
                        ->label('Catatan')
                        ->columnSpanFull(),
                ]);
        }

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $canApprove = Auth::user()->hasRole(['super_admin', 'hrd']);

                if (! $canApprove) {
                    $query->where('user_id', Auth::user()->id);
                }
            })
            ->columns([

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('leave_type')
                    ->label('Jenis Cuti')
                    ->formatStateUsing(fn (string $state): string => Leave::getLeaveTypes()[$state] ?? $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Tanggal Mulai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Tanggal Selesai')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return match ($record->status) {
                            'pending' => 'Menunggu',
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            default => $record->status,
                        };
                    })
                    ->color(function (string $state): string {
                        return match ($state) {
                            'Menunggu' => 'gray',
                            'Disetujui' => 'success',
                            'Ditolak' => 'danger',
                            'pending' => 'gray',
                            'approved' => 'success',
                            'rejected' => 'danger',
                            default => 'gray',
                        };
                    })
                    ->description(fn (Leave $record): ?string => $record->note ?? null)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Menunggu',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->placeholder('Semua Status'),

                Tables\Filters\SelectFilter::make('leave_type')
                    ->label('Jenis Cuti')
                    ->options(Leave::getLeaveTypes())
                    ->placeholder('Semua Jenis'),

                Tables\Filters\Filter::make('pending_approval')
                    ->label('Menunggu Persetujuan')
                    ->query(fn (Builder $query): Builder => $query->where('status', 'pending'))
                    ->toggle()
                    ->visible(fn (): bool => Auth::user()->hasRole(['super_admin', 'hrd'])),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(function (Leave $record) {
                        $record->update([
                            'status' => 'approved',
                            'note' => 'Disetujui oleh '.Auth::user()->name.' pada '.now()->format('d/m/Y H:i'),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Setujui Pengajuan Cuti')
                    ->modalDescription('Apakah Anda yakin ingin menyetujui pengajuan cuti ini?')
                    ->modalSubmitActionLabel('Ya, Setujui')
                    ->visible(fn (Leave $record): bool => Auth::user()->hasRole(['super_admin', 'hrd']) && $record->status === 'pending'
                    ),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->form([
                        Forms\Components\Textarea::make('rejection_note')
                            ->label('Alasan Penolakan')
                            ->required()
                            ->placeholder('Jelaskan alasan penolakan...'),
                    ])
                    ->action(function (Leave $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'note' => 'Ditolak oleh '.Auth::user()->name.' pada '.now()->format('d/m/Y H:i').
                                     '. Alasan: '.$data['rejection_note'],
                        ]);
                    })
                    ->modalHeading('Tolak Pengajuan Cuti')
                    ->modalSubmitActionLabel('Ya, Tolak')
                    ->visible(fn (Leave $record): bool => Auth::user()->hasRole(['super_admin', 'hrd']) && $record->status === 'pending'
                    ),

                Action::make('slip_cuti')
                    ->label('Slip Cuti')
                    ->color('info')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (Leave $record): string => route('leave.slip.download', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (Leave $record): bool => $record->status === 'approved'),

                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(fn (Leave $record): bool => Auth::user()->hasRole(['super_admin', 'hrd']) ||
                        (Auth::user()->id === $record->user_id && $record->status === 'pending')
                    ),
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
            'index' => Pages\ListLeaves::route('/'),
            'create' => Pages\CreateLeave::route('/create'),
            'edit' => Pages\EditLeave::route('/{record}/edit'),
        ];
    }
}
