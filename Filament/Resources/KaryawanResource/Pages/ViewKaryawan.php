<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewKaryawan extends ViewRecord
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit'),
            Actions\DeleteAction::make()
                ->label('Hapus'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Profil Karyawan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                ImageEntry::make('foto')
                                    ->label('Foto')
                                    ->circular()
                                    ->size(120)
                                    ->disk('public')
                                    ->columnSpan(1),
                                Grid::make(1)
                                    ->schema([
                                        TextEntry::make('user.name')
                                            ->label('Nama Lengkap')
                                            ->weight('bold')
                                            ->size('lg'),
                                        TextEntry::make('kode_karyawan')
                                            ->label('Kode Karyawan')
                                            ->copyable()
                                            ->badge()
                                            ->color('primary'),
                                        TextEntry::make('nip')
                                            ->label('NIP')
                                            ->copyable(),
                                        TextEntry::make('jabatan.nama_jabatan')
                                            ->label('Jabatan')
                                            ->badge()
                                            ->color('success'),
                                    ])
                                    ->columnSpan(2),
                            ]),
                    ]),

                Section::make('Data Pribadi')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('tempat_lahir')
                                    ->label('Tempat Lahir'),
                                TextEntry::make('tanggal_lahir')
                                    ->label('Tanggal Lahir')
                                    ->date('d M Y'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('jenis_kelamin')
                                    ->label('Jenis Kelamin')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state) {
                                        'Laki-laki' => 'info',
                                        'Perempuan' => 'warning',
                                        default => 'gray',
                                    }),
                                TextEntry::make('agama')
                                    ->label('Agama'),
                                TextEntry::make('no_ktp')
                                    ->label('No KTP')
                                    ->copyable(),
                            ]),
                    ]),

                Section::make('Informasi Kontak')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('no_hp')
                                    ->label('No HP')
                                    ->copyable()
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('no_telp')
                                    ->label('No Telepon')
                                    ->copyable()
                                    ->icon('heroicon-o-phone')
                                    ->placeholder('Tidak ada'),
                            ]),
                        TextEntry::make('alamat')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ]),

                Section::make('Informasi Bank')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('bank')
                                    ->label('Bank')
                                    ->placeholder('Tidak ada')
                                    ->icon('heroicon-o-building-library'),
                                TextEntry::make('no_rek')
                                    ->label('No Rekening')
                                    ->copyable()
                                    ->placeholder('Tidak ada'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->bank || $record->no_rek),

                Section::make('Informasi Gaji')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('gaji_pokok')
                                    ->label('Gaji Pokok')
                                    ->state(function ($record) {
                                        return $record->getGajiPokok();
                                    })
                                    ->money('IDR')
                                    ->color('success'),
                                TextEntry::make('total_tunjangan')
                                    ->label('Total Tunjangan')
                                    ->state(function ($record) {
                                        return $record->getTotalTunjangan();
                                    })
                                    ->money('IDR')
                                    ->color('info'),
                                TextEntry::make('total_gaji')
                                    ->label('Total Gaji')
                                    ->state(function ($record) {
                                        return $record->getTotalGaji();
                                    })
                                    ->money('IDR')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size('lg'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->jabatan),

                Section::make('Informasi Sistem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat Pada')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('updated_at')
                                    ->label('Diperbarui Pada')
                                    ->dateTime('d M Y H:i'),
                            ]),
                    ])
                    ->collapsible(),
            ]);
    }
}
