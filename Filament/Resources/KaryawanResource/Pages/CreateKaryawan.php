<?php

namespace App\Filament\Resources\KaryawanResource\Pages;

use App\Filament\Resources\KaryawanResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateKaryawan extends CreateRecord
{
    protected static string $resource = KaryawanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // If creating new user
        if (isset($data['create_new_user']) && $data['create_new_user']) {
            // Create new user
            $user = User::create([
                'name' => $data['user_name'],
                'email' => $data['user_email'],
                'password' => Hash::make($data['user_password']),
                'email_verified_at' => now(), // Auto verify email
            ]);

            // Assign role 'karyawan' to the new user
            try {
                $user->assignRole('karyawan');
            } catch (\Exception $e) {
                // If role doesn't exist, create it first
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'karyawan']);
                $user->assignRole('karyawan');
            }

            // Set user_id for karyawan
            $data['user_id'] = $user->id;
        }

        // Remove temporary fields
        unset($data['create_new_user'], $data['user_name'], $data['user_email'], $data['user_password']);

        return $data;
    }
}
