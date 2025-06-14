<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jabatan extends Model
{
    use HasFactory;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($jabatan) {
            if (empty($jabatan->kode_jabatan)) {
                $jabatan->kode_jabatan = static::generateKodeJabatan();
            }
        });
    }

    protected $table = 'jabatan';

    protected $fillable = [
        'kode_jabatan',
        'nama_jabatan',
        'gaji_pokok',
        'tunjangan_transportasi',
        'tunjangan_makan',
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'tunjangan_transportasi' => 'decimal:2',
        'tunjangan_makan' => 'decimal:2',
    ];

    protected $appends = [
        'total_gaji',
    ];

    // Relationships
    public function karyawan(): HasMany
    {
        return $this->hasMany(Karyawan::class);
    }

    // Accessors
    public function getTotalGajiAttribute(): float
    {
        return $this->gaji_pokok + $this->tunjangan_transportasi + $this->tunjangan_makan;
    }

    public function getJumlahKaryawanAttribute(): int
    {
        return $this->karyawan()->count();
    }

    public function getFormattedGajiPokokAttribute(): string
    {
        return 'Rp '.number_format($this->gaji_pokok, 0, ',', '.');
    }

    public function getFormattedTotalGajiAttribute(): string
    {
        return 'Rp '.number_format($this->total_gaji, 0, ',', '.');
    }

    // Scopes
    public function scopeWithKaryawan(Builder $query): Builder
    {
        return $query->with('karyawan');
    }

    public function scopeByGajiRange(Builder $query, float $min, float $max): Builder
    {
        return $query->whereBetween('gaji_pokok', [$min, $max]);
    }

    public function scopeHighSalary(Builder $query, float $threshold = 5000000): Builder
    {
        return $query->where('gaji_pokok', '>=', $threshold);
    }

    public function scopeLowSalary(Builder $query, float $threshold = 3000000): Builder
    {
        return $query->where('gaji_pokok', '<', $threshold);
    }

    public function scopePopular(Builder $query, int $minKaryawan = 5): Builder
    {
        return $query->has('karyawan', '>=', $minKaryawan);
    }

    // Methods
    public function calculateAnnualSalary(): float
    {
        return $this->total_gaji * 12;
    }

    public function getKaryawanByGender(): array
    {
        return [
            'laki_laki' => $this->karyawan()->where('jenis_kelamin', 'Laki-laki')->count(),
            'perempuan' => $this->karyawan()->where('jenis_kelamin', 'Perempuan')->count(),
        ];
    }

    public function getAverageAge(): float
    {
        $karyawan = $this->karyawan()->with('user')->get();

        if ($karyawan->isEmpty()) {
            return 0;
        }

        $totalAge = $karyawan->sum(function ($k) {
            return $k->umur;
        });

        return round($totalAge / $karyawan->count(), 1);
    }

    public function isHighPayingPosition(): bool
    {
        return $this->total_gaji >= 5000000;
    }

    public function hasKaryawan(): bool
    {
        return $this->karyawan()->exists();
    }

    /**
     * Generate unique kode jabatan with format JBT25001, JBT25002, etc.
     */
    public static function generateKodeJabatan(): string
    {
        $year = date('y'); // Get last 2 digits of current year
        $prefix = "JBT{$year}";

        // Get the last jabatan code for current year
        $lastJabatan = static::where('kode_jabatan', 'LIKE', "{$prefix}%")
            ->orderBy('kode_jabatan', 'desc')
            ->first();

        if ($lastJabatan) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastJabatan->kode_jabatan, -3);
            $newNumber = $lastNumber + 1;
        } else {
            // First jabatan for this year
            $newNumber = 1;
        }

        // Format with leading zeros (3 digits)
        return $prefix.str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }
}
