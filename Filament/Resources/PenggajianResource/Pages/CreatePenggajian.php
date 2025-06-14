<?php

namespace App\Filament\Resources\PenggajianResource\Pages;

use App\Filament\Resources\PenggajianResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePenggajian extends CreateRecord
{
    protected static string $resource = PenggajianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Debug: log the data being sent
        \Log::info('CreatePenggajian data:', $data);

        // Ensure karyawan_id is an integer (ID, not NIP)
        if (isset($data['karyawan_id']) && ! is_numeric($data['karyawan_id'])) {
            // If somehow a non-numeric value was sent, try to find the actual ID
            $karyawan = \App\Models\Karyawan::where('nip', $data['karyawan_id'])
                ->orWhere('kode_karyawan', $data['karyawan_id'])
                ->first();

            if ($karyawan) {
                $data['karyawan_id'] = $karyawan->id;
            }
        }

        return $data;
    }
}
