<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public int $created = 0;

    public int $updated = 0;

    public function collection(Collection $rows): void
    {
        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            $this->pushError(0, 'Role Siswa tidak ditemukan di database.');
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // header di baris 1

            $payload = [
                'id' => trim((string) ($row['id'] ?? $row['no_id'] ?? '')),
                'nis' => trim((string) ($row['nis'] ?? '')),
                'nisn' => trim((string) ($row['nisn'] ?? '')),
                'nama' => trim((string) ($row['nama'] ?? '')),
                'jenis_kelamin' => trim((string) ($row['jenis_kelamin'] ?? '')),
                'kelas_id' => $row['kelas_id'] ?? null,
                'email' => trim((string) ($row['email'] ?? '')),
                'username' => trim((string) ($row['username'] ?? '')),
                'password' => (string) ($row['password'] ?? ''),
            ];

            $validator = Validator::make($payload, [
                'nis' => 'required|string|max:50',
                'nisn' => 'required|string|max:50',
                'nama' => 'required|string|max:255',
                'jenis_kelamin' => 'required|string',
                'kelas_id' => 'required|integer',
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

            $kelas = Kelas::find($payload['kelas_id']);
            if (! $kelas) {
                $this->pushError($rowNumber, 'Kelas dengan ID ' . $payload['kelas_id'] . ' tidak ditemukan.');
                continue;
            }

            $siswa = null;
            if ($payload['id'] !== '') {
                $siswa = Siswa::find($payload['id']);
                if (! $siswa) {
                    $this->pushError($rowNumber, 'Siswa dengan ID ' . $payload['id'] . ' tidak ditemukan.');
                    continue;
                }
            }

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
                    'kelas_id' => $kelas->id,
                    'email' => $payload['email'],
                    'status_aktif' => true,
                ];

                if ($siswa) {
                    $siswa->update($siswaData);
                    $this->updated++;
                } else {
                    $siswa = Siswa::create($siswaData);
                    $this->created++;
                }

                $user = $siswa->user ?: User::where('siswa_id', $siswa->id)->first();

                if ($user) {
                    $user->update([
                        'name' => $payload['nama'],
                        'username' => $payload['username'],
                        'email' => $payload['email'],
                        'password' => Hash::make($payload['password']),
                        'jenis_kelamin' => $gender,
                        'role_id' => $roleSiswa->id,
                        'siswa_id' => $siswa->id,
                        'is_active' => true,
                    ]);
                } else {
                    User::create([
                        'name' => $payload['nama'],
                        'username' => $payload['username'],
                        'email' => $payload['email'],
                        'password' => Hash::make($payload['password']),
                        'jenis_kelamin' => $gender,
                        'role_id' => $roleSiswa->id,
                        'siswa_id' => $siswa->id,
                        'is_active' => true,
                    ]);
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
            ->when($ignore, function ($query) use ($ignore) {
                $query->where('id', '!=', $ignore->id);
            })
            ->exists();
    }

    protected function isNisnTaken(string $nisn, ?Siswa $ignore): bool
    {
        return Siswa::where('nisn', $nisn)
            ->when($ignore, function ($query) use ($ignore) {
                $query->where('id', '!=', $ignore->id);
            })
            ->exists();
    }

    protected function isUsernameTaken(string $username, ?Siswa $owner): bool
    {
        return User::where('username', $username)
            ->when($owner, function ($query) use ($owner) {
                $query->where('siswa_id', '!=', $owner->id);
            })
            ->exists();
    }

    protected function isSiswaEmailTaken(string $email, ?Siswa $owner): bool
    {
        return Siswa::where('email', $email)
            ->when($owner, function ($query) use ($owner) {
                $query->where('id', '!=', $owner->id);
            })
            ->exists();
    }

    protected function isUserEmailTaken(string $email, ?Siswa $owner): bool
    {
        return User::where('email', $email)
            ->when($owner, function ($query) use ($owner) {
                $query->where('siswa_id', '!=', $owner->id);
            })
            ->exists();
    }
}
