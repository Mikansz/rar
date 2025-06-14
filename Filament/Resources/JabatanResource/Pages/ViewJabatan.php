<?php

namespace App\Filament\Resources\JabatanResource\Pages;

use App\Filament\Resources\JabatanResource;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewJabatan extends ViewRecord
{
    protected static string $resource = JabatanResource::class;

    public function getTitle(): string
    {
        return "Detail Jabatan: {$this->record->nama_jabatan}";
    }

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
                Section::make('Informasi Jabatan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('kode_jabatan')
                                    ->label('Kode Jabatan')
                                    ->weight('bold')
                                    ->copyable(),
                                TextEntry::make('nama_jabatan')
                                    ->label('Nama Jabatan')
                                    ->weight('bold'),
                            ]),
                    ]),

                Section::make('Detail Kompensasi')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('gaji_pokok')
                                    ->label('Gaji Pokok')
                                    ->money('IDR')
                                    ->color('success'),
                                TextEntry::make('tunjangan_transportasi')
                                    ->label('Tunjangan Transportasi')
                                    ->money('IDR')
                                    ->color('info'),
                                TextEntry::make('tunjangan_makan')
                                    ->label('Tunjangan Makan')
                                    ->money('IDR')
                                    ->color('info'),
                            ]),
                    ]),

                Section::make('Statistik Karyawan')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('karyawan_laki_laki')
                                    ->label('Karyawan Laki-laki')
                                    ->state(function ($record) {
                                        return $record->karyawan()->where('jenis_kelamin', 'L')->count();
                                    })
                                    ->badge()
                                    ->color('info'),
                                TextEntry::make('karyawan_perempuan')
                                    ->label('Karyawan Perempuan')
                                    ->state(function ($record) {
                                        return $record->karyawan()->where('jenis_kelamin', 'P')->count();
                                    })
                                    ->badge()
                                    ->color('warning'),
                                TextEntry::make('total_karyawan')
                                    ->label('Total Karyawan')
                                    ->state(function ($record) {
                                        return $record->karyawan()->count();
                                    })
                                    ->badge()
                                    ->color('gray'),
                            ]),
                    ])
                    ->visible(fn ($record) => $record->karyawan()->exists()),

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
