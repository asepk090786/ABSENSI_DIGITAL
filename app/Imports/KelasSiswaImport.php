<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasSiswaImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public function __construct(private int $kelasId)
    {
    }

    public function headingRow(): int
    {
        return 1; // Header sekarang di baris 1
    }

    public function collection(Collection $rows): void
    {
        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            $this->pushError(0, 'Role Siswa tidak ditemukan di database.');
            return;
        }

        foreach ($rows as $index => $row) {
            // headingRow() di baris 1, jadi data mulai baris 2
            // $index dimulai dari 0, jadi baris actual = index + 2
            $rowNumber = $index + 2;

            // Skip baris kosong
            if (empty($row['nis']) && empty($row['nama'])) {
                continue;
            }

            $payload = [
                'nis' => trim((string) ($row['nis'] ?? '')),
                'nisn' => trim((string) ($row['nisn'] ?? '')),
                'nama' => trim((string) ($row['nama'] ?? '')),
                'jenis_kelamin' => trim((string) ($row['jenis_kelamin'] ?? '')),
                'email' => trim((string) ($row['email'] ?? '')),
                'username' => trim((string) ($row['username'] ?? '')),
                'password' => (string) ($row['password'] ?? ''),
            ];

            $validator = Validator::make($payload, [
                'nis' => 'required|string|max:50',
                'nisn' => 'required|string|max:50',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|string',
                'email' => 'required|email|max:255',
                'username' => 'required|string|max:255',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            $gender = $this->normalizeGender($payload['jenis_kelamin']);
            if ($gender === null) {
                $this->pushError($rowNumber, 'Kolom jenis_kelamin hanya boleh berisi L atau P.');
                continue;
            }

            // Selalu cari siswa berdasarkan NIS dan kelas, jangan gunakan ID
            $siswa = Siswa::where('nis', $payload['nis'])
                ->where('kelas_id', $this->kelasId)
                ->first();

            if ($this->isNisTaken($payload['nis'], $siswa)) {
                $this->pushError($rowNumber, 'NIS ' . $payload['nis'] . ' sudah terdaftar.');
                continue;
            }

            if ($this->isNisnTaken($payload['nisn'], $siswa)) {
                $this->pushError($rowNumber, 'NISN ' . $payload['nisn'] . ' sudah terdaftar.');
                continue;
            }

            if ($this->isSiswaEmailTaken($payload['email'], $siswa)) {
                $this->pushError($rowNumber, 'Email siswa sudah digunakan.');
                continue;
            }

            if ($this->isUserEmailTaken($payload['email'], $siswa)) {
                $this->pushError($rowNumber, 'Email sudah digunakan.');
                continue;
            }

            if ($this->isUsernameTaken($payload['username'], $siswa)) {
                $this->pushError($rowNumber, 'Username sudah digunakan.');
                continue;
            }

            try {
                $siswaData = [
                    'nis' => $payload['nis'],
                    'nisn' => $payload['nisn'],
                    'nama' => $payload['nama'],
                    'jenis_kelamin' => $gender,
                    'kelas_id' => $this->kelasId,
                    'email' => $payload['email'],
                    'status_aktif' => true,
                ];

                if ($siswa) {
                    $siswa->update($siswaData);
                } else {
                    $siswa = Siswa::create($siswaData);
                }

                $user = $siswa->user ?: User::where('siswa_id', $siswa->id)->first();

                $userData = [
                    'name' => $payload['nama'],
                    'username' => $payload['username'],
                    'email' => $payload['email'],
                    'password' => Hash::make($payload['password']),
                    'jenis_kelamin' => $gender,
                    'role_id' => $roleSiswa->id,
                    'siswa_id' => $siswa->id,
                    'is_active' => true,
                ];

                if ($user) {
                    $user->update($userData);
                } else {
                    User::create($userData);
                }
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function pushError(int $row, string $message): void
    {
        $this->errors[] = 'Baris ' . $row . ': ' . $message;
    }

    protected function normalizeGender(string $raw): ?string
    {
        $value = strtoupper(substr(trim($raw), 0, 1));
        return in_array($value, ['L', 'P'], true) ? $value : null;
    }

    protected function isNisTaken(string $nis, ?Siswa $ignore): bool
    {
        return Siswa::where('nis', $nis)
            ->when($ignore, fn($q) => $q->where('id', '!=', $ignore->id))
            ->exists();
    }

    protected function isNisnTaken(string $nisn, ?Siswa $ignore): bool
    {
        return Siswa::where('nisn', $nisn)
            ->when($ignore, fn($q) => $q->where('id', '!=', $ignore->id))
            ->exists();
    }

    protected function isUsernameTaken(string $username, ?Siswa $owner): bool
    {
        return User::where('username', $username)
            ->when($owner, fn($q) => $q->where('siswa_id', '!=', $owner->id))
            ->exists();
    }

    protected function isSiswaEmailTaken(string $email, ?Siswa $owner): bool
    {
        return Siswa::where('email', $email)
            ->when($owner, fn($q) => $q->where('id', '!=', $owner->id))
            ->exists();
    }

    protected function isUserEmailTaken(string $email, ?Siswa $owner): bool
    {
        return User::where('email', $email)
            ->when($owner, fn($q) => $q->where('siswa_id', '!=', $owner->id))
            ->exists();
    }
}
