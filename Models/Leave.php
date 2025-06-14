<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Leave extends Model
{
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    // Konstanta untuk jenis cuti
    public const CUTI_TAHUNAN = 'cuti_tahunan';

    public const CUTI_SAKIT = 'cuti_sakit';

    public const CUTI_MELAHIRKAN = 'cuti_melahirkan';

    public const CUTI_PENTING = 'cuti_penting';

    public const CUTI_BESAR = 'cuti_besar';

    public const IZIN = 'izin';

    // Konstanta untuk jenis izin
    public const IZIN_KELUAR_KANTOR = 'keluar_kantor';

    public const IZIN_DATANG_TERLAMBAT = 'datang_terlambat';

    public const IZIN_PULANG_CEPAT = 'pulang_cepat';

    public const IZIN_TIDAK_MASUK = 'tidak_masuk';

    public const IZIN_KEPERLUAN_KELUARGA = 'keperluan_keluarga';

    public const IZIN_URUSAN_PRIBADI = 'urusan_pribadi';

    protected $fillable = [
        'user_id',
        'leave_type',
        'permission_type',
        'start_date',
        'end_date',
        'permission_start_time',
        'permission_end_time',
        'reason',
        'sick_certificate',
        'symptoms',
        'doctor_name',
        'hospital_clinic',
        'is_emergency',
        'status',
        'note',
        'approved_by',
        'approved_at',
        'approval_note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_emergency' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'days_requested',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Daftar jenis cuti untuk dropdown
    public static function getLeaveTypes(): array
    {
        return [
            self::CUTI_TAHUNAN => 'Cuti Tahunan',
            self::CUTI_SAKIT => 'Cuti Sakit',
            self::CUTI_MELAHIRKAN => 'Cuti Melahirkan',
            self::CUTI_PENTING => 'Cuti Penting',
            self::CUTI_BESAR => 'Cuti Besar',
            self::IZIN => 'Izin',
        ];
    }

    // Daftar jenis izin untuk dropdown
    public static function getPermissionTypes(): array
    {
        return [
            self::IZIN_KELUAR_KANTOR => 'Keluar Kantor Sementara',
            self::IZIN_DATANG_TERLAMBAT => 'Datang Terlambat',
            self::IZIN_PULANG_CEPAT => 'Pulang Lebih Cepat',
            self::IZIN_TIDAK_MASUK => 'Tidak Masuk Kerja',
            self::IZIN_KEPERLUAN_KELUARGA => 'Keperluan Keluarga',
            self::IZIN_URUSAN_PRIBADI => 'Urusan Pribadi',
        ];
    }

    // Check if this is a permission (izin)
    public function isPermission(): bool
    {
        return $this->leave_type === self::IZIN;
    }

    // Check if this is sick leave
    public function isSickLeave(): bool
    {
        return $this->leave_type === self::CUTI_SAKIT;
    }

    // Get sick certificate URL
    public function getSickCertificateUrlAttribute(): ?string
    {
        if ($this->sick_certificate) {
            return Storage::url($this->sick_certificate);
        }

        return null;
    }

    // Get duration in hours for permission
    public function getPermissionDurationAttribute(): ?string
    {
        if ($this->permission_start_time && $this->permission_end_time) {
            try {
                $start = \Carbon\Carbon::createFromTimeString($this->permission_start_time);
                $end = \Carbon\Carbon::createFromTimeString($this->permission_end_time);
                $duration = $end->diffInHours($start);

                return $duration.' jam';
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    // Get days requested attribute
    public function getDaysRequestedAttribute(): int
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->diffInDays($this->end_date) + 1;
        }

        return 1; // Default untuk izin harian
    }
}
