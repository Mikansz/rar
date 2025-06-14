<?php

namespace App\Filament\Resources\OfficeResource\Pages;

use App\Filament\Resources\OfficeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffice extends CreateRecord
{
    use FooterScript;

    protected static string $resource = OfficeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Empty array to maintain header layout
        ];
    }

    public function getHeadComponents(): array
    {
        return [
            // Add search script
        ];
    }

    // Footer is provided by the FooterScript trait
}
