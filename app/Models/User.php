<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        // Role mahasiswa hanya mengakses portal Breeze frontend, bukan panel admin Filament
        if ($this->hasRole('mahasiswa') && !$this->hasAnyRole([
            'super_admin', 'admin_fakultas', 'dekan', 'wakil_dekan', 
            'kabag_tu', 'keuangan', 'admin_prodi', 'ka_prodi', 'dosen'
        ])) {
            return false;
        }

        return true;
    }

    public function userAccesses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserAccess::class);
    }

    public function accessibleStudyProgramIds(): array
    {
        // Role tingkat fakultas/global dapat mengakses seluruh prodi
        if ($this->hasAnyRole(['super_admin', 'admin_fakultas', 'dekan', 'wakil_dekan', 'kabag_tu', 'keuangan'])) {
            return StudyProgram::pluck('id')->toArray();
        }

        // Role berbasis prodi dibatasi oleh user_access
        return $this->userAccesses()->pluck('study_program_id')->toArray();
    }

    public function registrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
}
