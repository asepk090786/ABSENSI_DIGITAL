<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersImport implements ToCollection
{
    public int $created = 0;

    /** @var array<int, string> */
    private const ROLE_CODE_MAP = [
        '1' => 'Guru BK',
        '2' => 'Guru Kelas',
        '3' => 'Guru Mapel',
        '4' => 'Kepala Sekolah',
        '5' => 'Petugas Keamanan',
        '6' => 'Siswa',
    ];

    /** @var array<int, string> */
    public array $errors = [];

    public function collection(Collection $rows): void
    {
        $dataStartRow = null;

        // Find where actual data starts (skip header and reference table)
        foreach ($rows as $index => $row) {
            $col1 = trim((string) ($row[0] ?? ''));
            if ($col1 === 'NO') {
                $dataStartRow = $index + 1; // Next row after header
                break;
            }
        }

        if ($dataStartRow === null) {
            $this->pushError(0, 'Header dengan kolom NO tidak ditemukan.');
            return;
        }

        // Process only the actual data rows
        foreach ($rows->slice($dataStartRow) as $index => $row) {
            $rowNumber = $dataStartRow + $index + 1; // Actual row number in Excel

            $no = trim((string) ($row[0] ?? ''));
            $name = trim((string) ($row[1] ?? ''));
            $gender = strtoupper(trim((string) ($row[2] ?? '')));
            $username = trim((string) ($row[3] ?? ''));
            $password = (string) ($row[4] ?? '');
            $email = trim((string) ($row[5] ?? ''));
            $roleInput = trim((string) ($row[6] ?? ''));

            // Stop if we hit the reference table section (PERAN with lowercase)
            if ($no === 'PERAN' || $name === 'PERAN' || $name === '') {
                break;
            }

            // Validation
            if ($name === '' || $gender === '' || $username === '' || $password === '' || $roleInput === '') {
                $this->pushError($rowNumber, 'Data wajib belum lengkap (nama, jenis kelamin, username, password, peran).');
                continue;
            }

            if (! in_array($gender, ['L', 'P'], true)) {
                $this->pushError($rowNumber, 'Kolom L/P hanya boleh berisi L atau P.');
                continue;
            }

            $roleId = $this->resolveRoleId($roleInput);
            if (! $roleId) {
                $this->pushError($rowNumber, 'Peran tidak ditemukan di sistem.');
                continue;
            }

            if (User::where('username', $username)->exists()) {
                $this->pushError($rowNumber, 'Username sudah digunakan.');
                continue;
            }

            if ($email !== '' && User::where('email', $email)->exists()) {
                $this->pushError($rowNumber, 'Email sudah digunakan.');
                continue;
            }

            $role = Role::find($roleId);

            $user = User::create([
                'name' => $name,
                'username' => $username,
                'email' => $email !== '' ? $email : null,
                'password' => Hash::make($password),
                'jenis_kelamin' => $gender,
                'role_id' => $roleId,
                'is_active' => true,
                'guru_id' => $this->guessGuruId($role, $name),
                'kepala_sekolah_id' => $this->guessKepalaSekolahId($role, $name),
                'siswa_id' => $this->guessSiswaId($role, $name),
            ]);

            if ($user) {
                $this->created++;
            }
        }
    }

    private function resolveRoleId(string $input): ?int
    {
        $candidate = self::ROLE_CODE_MAP[$input] ?? $input;

        $role = Role::whereRaw('LOWER(role_name) = ?', [Str::lower($candidate)])->first();

        return $role?->id;
    }

    private function guessGuruId(?Role $role, string $name): ?int
    {
        if (! $role || ! Str::contains(Str::lower($role->role_name), 'guru')) {
            return null;
        }

        $guru = Guru::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();

        return $guru?->id;
    }

    private function guessKepalaSekolahId(?Role $role, string $name): ?int
    {
        if (! $role || ! Str::contains(Str::lower($role->role_name), 'kepala')) {
            return null;
        }

        $kepala = KepalaSekolah::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();

        return $kepala?->id;
    }

    private function guessSiswaId(?Role $role, string $name): ?int
    {
        if (! $role || ! Str::contains(Str::lower($role->role_name), 'siswa')) {
            return null;
        }

        $siswa = Siswa::whereRaw('LOWER(nama) = ?', [Str::lower($name)])->first();

        return $siswa?->id;
    }

    private function pushError(int $rowNumber, string $message): void
    {
        $this->errors[] = "Baris {$rowNumber}: {$message}";
    }
}
