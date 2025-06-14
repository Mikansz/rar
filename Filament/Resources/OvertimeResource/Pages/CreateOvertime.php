<?php

namespace App\Filament\Resources\OvertimeResource\Pages;

use App\Filament\Resources\OvertimeResource;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;

class CreateOvertime extends CreateRecord
{
    protected static string $resource = OvertimeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate hours if not provided or recalculate based on times
        if (isset($data['start_time']) && isset($data['end_time'])) {
            $start = Carbon::parse($data['start_time']);
            $end = Carbon::parse($data['end_time']);

            // If end time is earlier than start time, assume it's next day
            if ($end->lessThan($start)) {
                $end->addDay();
            }

            $data['hours'] = $start->diffInMinutes($end) / 60;
        }

        // Calculate total amount
        if (isset($data['hours']) && isset($data['rate_per_hour'])) {
            $data['total_amount'] = $data['hours'] * $data['rate_per_hour'];
        }

        return $data;
    }
}
