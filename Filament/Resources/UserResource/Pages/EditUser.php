<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Get the current user's primary role
        $user = $this->record;
        $data['role'] = $user->getPrimaryRole();

        return $data;
    }

    protected function afterSave(): void
    {
        $user = $this->record;
        $role = $this->data['role'] ?? $user->getPrimaryRole() ?? 'karyawan';

        // Assign single role to the user
        $user->assignSingleRole($role);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Remove role from data as it's handled separately
        unset($data['role']);

        return $data;
    }
}
