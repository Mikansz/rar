<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Imports\KaryawanImport;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ImportKaryawan extends Page
{
    protected static string $resource = KaryawanResource::class;

    protected static string $view = 'filament.resources.karyawan-resource.pages.import-karyawan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Upload Data Karyawan')
                    ->schema([
                        FileUpload::make('file')
                            ->label('File Excel')
                            ->helperText('Format file: XLS, XLSX, CSV')
                            ->disk('public')
                            ->directory('imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                            ])
                            ->required(),
                    ])
                    ->description('Upload file excel untuk mengimpor data karyawan'),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('import')
                ->label('Import Data')
                ->submit('import'),

            Action::make('downloadTemplate')
                ->label('Download Template')
                ->color('gray')
                ->url(route('karyawan.template'))
                ->openUrlInNewTab(),
        ];
    }

    public function import()
    {
        $data = $this->form->getState();

        try {
            Log::info('Importing karyawan data', ['filename' => $data['file']]);
            $filePath = storage_path('app/public/'.$data['file']);

            $import = new KaryawanImport;
            Excel::import($import, $filePath);

            if (count($import->failures()) > 0) {
                $errors = [];
                foreach ($import->failures() as $failure) {
                    $errors[] = 'Baris '.$failure->row().': '.implode(', ', $failure->errors());
                }

                $errorMessage = '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';

                Notification::make()
                    ->title('Ada kesalahan pada beberapa data')
                    ->body($errorMessage)
                    ->danger()
                    ->send();

                return;
            }

            Notification::make()
                ->title('Data berhasil diimpor')
                ->success()
                ->send();

            return redirect()->route('filament.backoffice.resources.karyawans.index');
        } catch (\Exception $e) {
            Log::error('Import karyawan error: '.$e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title('Terjadi kesalahan saat import')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
