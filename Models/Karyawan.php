<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Karyawan extends Model
{
    use HasFactory;

    protected $table = 'karyawan';

    protected $fillable = [
        'nip',
        'user_id',
        'kode_karyawan',
        'tempat_lahir',
        'tanggal_lahir',
        'no_ktp',
        'jenis_kelamin',
        'agama',
        'no_hp',
        'no_telp',
        'alamat',
        'bank',
        'no_rek',
        'foto',
        'jabatan_id',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    protected $appends = [
        'nama_lengkap',
        'status_karyawan',
        'foto_url',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jabatan(): BelongsTo
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_id');
    }

    public function penggajian(): HasMany
    {
        return $this->hasMany(Penggajian::class, 'karyawan_id', 'id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'user_id', 'user_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_id', 'user_id');
    }

    // Accessors
    public function getNamaLengkapAttribute(): string
    {
        return $this->user?->name ?? 'N/A';
    }

    public function getUmurAttribute(): int
    {
        return $this->tanggal_lahir ? Carbon::parse($this->tanggal_lahir)->age : 0;
    }

    public function getStatusKaryawanAttribute(): string
    {
        return $this->jabatan?->nama_jabatan ?? 'Tidak Ada Jabatan';
    }

    public function getFotoUrlAttribute(): ?string
    {
        return $this->foto ? url('storage/'.$this->foto) : null;
    }

    // Scopes
    public function scopeByJabatan(Builder $query, $jabatanId): Builder
    {
        return $query->where('jabatan_id', $jabatanId);
    }

    public function scopeByJenisKelamin(Builder $query, string $jenisKelamin): Builder
    {
        return $query->where('jenis_kelamin', $jenisKelamin);
    }

    public function scopeByAgama(Builder $query, string $agama): Builder
    {
        return $query->where('agama', $agama);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereHas('user', function ($q) {
            $q->whereNotNull('email_verified_at');
        });
    }

    // Methods
    public function getGajiPokok(): float
    {
        return $this->jabatan?->gaji_pokok ?? 0;
    }

    public function getTotalTunjangan(): float
    {
        // Ambil total tunjangan dari penggajian terbaru
        $penggajianTerbaru = $this->penggajian()->latest()->first();

        if (! $penggajianTerbaru) {
            return 0;
        }

        return $penggajianTerbaru->tunjangan_transport +
               $penggajianTerbaru->tunjangan_makan +
               $penggajianTerbaru->tunjangan_komunikasi +
               $penggajianTerbaru->tunjangan_kesehatan +
               $penggajianTerbaru->tunjangan_lembur +
               $penggajianTerbaru->tunjangan_hari_raya +
               $penggajianTerbaru->tunjangan_insentif +
               $penggajianTerbaru->tunjangan_lainnya;
    }

    public function getTotalGaji(): float
    {
        $penggajianTerbaru = $this->penggajian()->latest()->first();

        if (! $penggajianTerbaru) {
            return $this->getGajiPokok();
        }

        return $penggajianTerbaru->total_gaji;
    }
}
