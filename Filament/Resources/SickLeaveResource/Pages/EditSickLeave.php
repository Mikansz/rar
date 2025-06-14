<?php

namespace App\Filament\Resources\SickLeaveResource\Pages;

use App\Filament\Resources\SickLeaveResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSickLeave extends EditRecord
{
    protected static string $resource = SickLeaveResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
