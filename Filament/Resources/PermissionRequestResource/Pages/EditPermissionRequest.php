<?php

namespace App\Filament\Resources\PermissionRequestResource\Pages;

use App\Filament\Resources\PermissionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermissionRequest extends EditRecord
{
    protected static string $resource = PermissionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
