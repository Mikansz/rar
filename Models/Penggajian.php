<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penggajian extends Model
{
    use HasFactory;

    protected $table = 'penggajian';

    protected $fillable = [
        'karyawan_id',
        'periode',
        'gaji_pokok',
        'tunjangan_transport',
        'tunjangan_makan',
        'tunjangan_komunikasi',
        'tunjangan_kesehatan',
        'tunjangan_lembur',
        'tunjangan_hari_raya',
        'tunjangan_insentif',
        'tunjangan_lainnya',
        'jam_lembur',
        'jumlah_hadir',
        'potongan_absen',
        'potongan_kasbon',
        'potongan_tidak_hadir',
        'potongan_penyesuaian_lainnya',
        'potongan_pph21',
        'total_gaji',
        'keterangan',
        'status',
        'approved_by',
        'approval_note',
        'approved_at',
    ];

    protected $casts = [
        'periode' => 'date',
        'approved_at' => 'datetime',
        'gaji_pokok' => 'decimal:2',
        'tunjangan_transport' => 'decimal:2',
        'tunjangan_makan' => 'decimal:2',
        'tunjangan_komunikasi' => 'decimal:2',
        'tunjangan_kesehatan' => 'decimal:2',
        'tunjangan_lembur' => 'decimal:2',
        'tunjangan_hari_raya' => 'decimal:2',
        'tunjangan_insentif' => 'decimal:2',
        'tunjangan_lainnya' => 'decimal:2',
        'jam_lembur' => 'integer',
        'jumlah_hadir' => 'integer',
        'potongan_absen' => 'decimal:2',
        'potongan_kasbon' => 'decimal:2',
        'potongan_tidak_hadir' => 'decimal:2',
        'potongan_penyesuaian_lainnya' => 'decimal:2',
        'potongan_pph21' => 'decimal:2',
        'total_gaji' => 'decimal:2',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
