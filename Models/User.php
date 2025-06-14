<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

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

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('storage/'.$this->image) : null;
    }

    public function karyawan(): HasOne
    {
        return $this->hasOne(Karyawan::class);
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    /**
     * Assign a single role to the user, removing all other roles
     */
    public function assignSingleRole(string $role): void
    {
        // Remove all existing roles
        $this->syncRoles([]);

        // Assign the new role
        $this->assignRole($role);
    }

    /**
     * Get the primary role of the user (first role if multiple exist)
     */
    public function getPrimaryRole(): ?string
    {
        return $this->roles()->first()?->name;
    }

    /**
     * Check if user has multiple roles (for validation)
     */
    public function hasMultipleRoles(): bool
    {
        return $this->roles()->count() > 1;
    }
}
