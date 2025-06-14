<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'start_time',
        'end_time',
        'hours',
        'reason',
        'type',
        'rate_per_hour',
        'total_amount',
        'status',
        'approval_note',
        'is_calculated',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'rate_per_hour' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_calculated' => 'boolean',
    ];

    // Overtime types
    public const TYPE_WEEKDAY = 'weekday';

    public const TYPE_WEEKEND = 'weekend';

    public const TYPE_HOLIDAY = 'holiday';

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate overtime hours based on start and end time
     */
    public function calculateHours(): float
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // If end time is earlier than start time, assume it's next day
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        return $start->diffInMinutes($end) / 60;
    }

    /**
     * Calculate total overtime amount based on hours and rate
     */
    public function calculateAmount(): float
    {
        return $this->hours * $this->rate_per_hour;
    }

    /**
     * Get overtime type based on date
     */
    public static function getOvertimeType(Carbon $date): string
    {
        // You can customize this logic based on your company's policy
        if ($date->isWeekend()) {
            return self::TYPE_WEEKEND;
        }

        // Add holiday checking logic here if needed
        // For now, we'll just check weekends

        return self::TYPE_WEEKDAY;
    }

    /**
     * Get default rate per hour based on overtime type
     */
    public static function getDefaultRate(string $type): float
    {
        // These rates can be configured in settings
        return match ($type) {
            self::TYPE_WEEKDAY => 25000, // 1.5x regular hourly rate
            self::TYPE_WEEKEND => 30000, // 2x regular hourly rate
            self::TYPE_HOLIDAY => 35000, // 2.5x regular hourly rate
            default => 25000,
        };
    }

    /**
     * Get overtime types for dropdown
     */
    public static function getOvertimeTypes(): array
    {
        return [
            self::TYPE_WEEKDAY => 'Hari Kerja',
            self::TYPE_WEEKEND => 'Akhir Pekan',
            self::TYPE_HOLIDAY => 'Hari Libur',
        ];
    }

    /**
     * Get status options for dropdown
     */
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Persetujuan',
            self::STATUS_APPROVED => 'Disetujui',
            self::STATUS_REJECTED => 'Ditolak',
        ];
    }
}
