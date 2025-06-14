<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $user = $this->record;
        $role = $this->data['role'] ?? 'karyawan';

        // Assign single role to the user
        $user->assignSingleRole($role);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default role if none provided
        if (empty($data['role'])) {
            $data['role'] = 'karyawan';
        }

        // Remove role from data as it's handled separately
        unset($data['role']);

        return $data;
    }
}
