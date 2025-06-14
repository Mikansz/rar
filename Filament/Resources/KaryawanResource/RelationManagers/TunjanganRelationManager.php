<?php

namespace App\Filament\Resources\KaryawanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TunjanganRelationManager extends RelationManager
{
    protected static string $relationship = 'tunjangan';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('bulan')
                    ->label('Bulan Periode')
                    ->required()
                    ->default(now()),
                Forms\Components\TextInput::make('jumlah_lembur')
                    ->label('Jumlah Lembur (Jam)')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('jumlah_hadir')
                    ->label('Jumlah Kehadiran (Hari)')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('tunjangan_transport')
                    ->label('Tunjangan Transportasi')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('tunjangan_makan')
                    ->label('Tunjangan Makan')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('jumlah_uang_lembur')
                    ->label('Jumlah Uang Lembur')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\TextInput::make('total_tunjangan')
                    ->label('Total Tunjangan')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->default(0),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('bulan')
            ->columns([
                Tables\Columns\TextColumn::make('bulan')
                    ->label('Bulan')
                    ->date('F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_lembur')
                    ->label('Jam Lembur')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_hadir')
                    ->label('Hari Hadir')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tunjangan_transport')
                    ->label('Tunjangan Transport')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('tunjangan_makan')
                    ->label('Tunjangan Makan')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('jumlah_uang_lembur')
                    ->label('Uang Lembur')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_tunjangan')
                    ->label('Total Tunjangan')
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
                    })
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
