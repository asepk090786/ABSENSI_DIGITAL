<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Siswa;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nip',
        'username',
        'email',
        'password',
        'jenis_kelamin',
        'foto',
        'is_active',
        'role_id',
        'guru_id',
        'kepala_sekolah_id',
        'siswa_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kepalaSekolah()
    {
        return $this->belongsTo(KepalaSekolah::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function getClassPosition(): ?string
    {
        if (! $this->siswa) {
            return null;
        }

        $position = data_get($this->siswa, 'jabatan_kelas') ?? data_get($this->siswa, 'jabatan');

        if (! is_string($position) || trim($position) === '') {
            return null;
        }

        return mb_strtolower(trim($position));
    }

    public function hasClassPosition(): bool
    {
        return in_array($this->getClassPosition(), ['ketua', 'wakil', 'sekretaris'], true);
    }

    public function roleNames(): array
    {
        $names = collect();

        if ($this->role && $this->role->role_name) {
            $names->push($this->role->role_name);
        }

        $additionalRoles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->get();

        $names = $names->merge($additionalRoles->pluck('role_name'));

        return $names
            ->filter()
            ->map(fn ($name) => trim($name))
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    protected static array $roleEquivalents = [
        'Kepala Sekolah' => ['Kepala Sekolah', 'Pengawas Pembina'],
        'Pengawas Pembina' => ['Pengawas Pembina', 'Kepala Sekolah'],
    ];

    public function hasRole(string $roleName): bool
    {
        $needle = mb_strtolower(trim($roleName));
        $roleNames = collect($this->roleNames())->map(fn ($name) => mb_strtolower(trim($name)));

        if (isset(self::$roleEquivalents[trim($roleName)])) {
            $equivalentNames = collect(self::$roleEquivalents[trim($roleName)])
                ->map(fn ($name) => mb_strtolower(trim($name)));

            return $roleNames->intersect($equivalentNames)->isNotEmpty();
        }

        return $roleNames->contains($needle);
    }

    public function hasAnyRole(array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($roleName)) {
                return true;
            }
        }

        return false;
    }
}
