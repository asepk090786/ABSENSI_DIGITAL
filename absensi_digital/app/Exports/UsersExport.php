<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $rowNumber = 1;

    private const ROLE_CODES = [
        'Guru BK' => 1,
        'Guru Kelas' => 2,
        'Guru Mapel' => 3,
        'Kepala Sekolah' => 4,
        'Petugas Keamanan' => 5,
        'Siswa' => 6,
    ];

    public function collection(): Collection
    {
        return User::with('role')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['NO', 'NAMA LENGKAP', 'L/P', 'USERNAME', 'PASSWORD', 'EMAIL', 'PERAN'];
    }

    public function map($user): array
    {
        return [
            $this->rowNumber++,
            $user->name,
            strtoupper($user->jenis_kelamin ?? ''),
            $user->username,
            '', // password left blank for security
            $user->email ?? '',
            $this->resolveRoleCode($user),
        ];
    }

    private function resolveRoleCode(User $user)
    {
        $roleName = $user->role->role_name ?? '';

        if (isset(self::ROLE_CODES[$roleName])) {
            return self::ROLE_CODES[$roleName];
        }

        return $roleName;
    }
}
