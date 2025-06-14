<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Download Data')
                ->label('Unduh Data')
                ->url(route('attendance-export'))
                ->color('primary'),
            Action::make('Tambah Presensi')
                ->url(route('presensi'))
                ->color('success'),

            Actions\CreateAction::make()->label('Buat Absensi'),
        ];
    }
}
