<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Imports\KaryawanImport;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListKaryawans extends ListRecords
{
    protected static string $resource = KaryawanResource::class;

    public function mount(): void
    {
        parent::mount();

        // Show flash message if exists
        if (session()->has('success')) {
            Notification::make()
                ->title(session('success'))
                ->success()
                ->send();
        }

        if (session()->has('error')) {
            Notification::make()
                ->title(session('error'))
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Karyawan'),
            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(route('karyawan.export'))
                ->color('success'),
            Action::make('template')
                ->label('Download Template')
                ->icon('heroicon-o-document-arrow-down')
                ->url(route('karyawan.template'))
                ->color('warning'),
            Action::make('import')
                ->label('Import')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('File Excel')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                        ->helperText('Upload file Excel (.xlsx, .xls) atau CSV dengan format sesuai template'),
                ])
                ->action(function (array $data) {
                    try {
                        $file = $data['file'];

                        if (! $file) {
                            Notification::make()
                                ->title('File tidak ditemukan')
                                ->danger()
                                ->send();

                            return;
                        }

                        // Get file path dari storage
                        $filePath = storage_path('app/public/'.$file);

                        // Proses import
                        Excel::import(new KaryawanImport, $filePath);

                        Notification::make()
                            ->title('Import berhasil!')
                            ->body('Data karyawan telah berhasil diimpor.')
                            ->success()
                            ->send();

                        // Refresh halaman
                        $this->redirect(request()->header('Referer'));

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import gagal!')
                            ->body('Terjadi kesalahan: '.$e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
