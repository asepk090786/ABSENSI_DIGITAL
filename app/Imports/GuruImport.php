<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class GuruImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $updated = 0;
    protected $created = 0;
    protected $mode;

    public function __construct(?string $mode = 'create')
    {
        $this->mode = in_array($mode, ['create', 'update'], true) ? $mode : 'create';
    }

    public function collection(Collection $rows)
    {
        $roleGuru = Role::whereIn('role_name', ['Guru', 'Guru Mapel', 'Guru Kelas'])
            ->orderByRaw("CASE role_name WHEN 'Guru' THEN 1 WHEN 'Guru Mapel' THEN 2 WHEN 'Guru Kelas' THEN 3 ELSE 99 END")
            ->first();

        if (!$roleGuru) {
            $this->pushError(0, 'Role Guru tidak ditemukan di database.');
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $jenisKelamin = $this->normalizeJenisKelamin($row['jenis_kelamin'] ?? null);
                $kodeGuru = trim((string) ($row['kode_guru'] ?? '')) ?: null;
                $email = trim((string) ($row['email'] ?? ''));
                if (empty($email) && !empty($kodeGuru)) {
                    $email = 'guru' . $kodeGuru . '@simadis.sch';
                }

                $username = trim((string) ($row['username'] ?? ''));
                if (empty($username) && !empty($kodeGuru)) {
                    $username = 'guru' . $kodeGuru;
                }
                $password = trim((string) ($row['password'] ?? ''));
                if (empty($password) && !empty($kodeGuru)) {
                    $password = 'guru' . $kodeGuru;
                }

                $existingGuru = $this->findExistingGuru($row, $kodeGuru, $email, $username);

                if ($this->mode === 'update') {
                    if (!$existingGuru) {
                        $this->pushError($rowNumber, 'Data guru tidak ditemukan. Gunakan kolom id_guru, kode_guru, NIP, atau email yang sesuai untuk update.');
                        continue;
                    }

                    $validator = Validator::make([
                        'nama' => $row['nama'] ?? null,
                        'jenis_kelamin' => $jenisKelamin,
                        'email' => $email,
                    ], [
                        'nama' => 'required|string|max:255',
                        'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan',
                        'email' => 'required|email|max:255',
                    ]);

                    if ($validator->fails()) {
                        $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                        continue;
                    }

                    $this->updateGuru($existingGuru, $row, $jenisKelamin, $email, $username, $password, $roleGuru);
                    $this->updated++;
                    continue;
                }

                $validator = Validator::make([
                    'nama' => $row['nama'] ?? null,
                    'jenis_kelamin' => $jenisKelamin,
                    'username' => $username,
                    'password' => $password,
                    'email' => $email,
                ], [
                    'nama' => 'required|string|max:255',
                    'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan',
                    'username' => 'required|string|max:255',
                    'password' => 'required|string|min:6',
                    'email' => 'required|email|max:255',
                ]);

                if ($validator->fails()) {
                    $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                    continue;
                }

                if ($existingGuru) {
                    $this->updateGuru($existingGuru, $row, $jenisKelamin, $email, $username, $password, $roleGuru);
                    $this->updated++;
                } else {
                    $this->createGuru($row, $jenisKelamin, $kodeGuru, $email, $username, $password, $roleGuru);
                    $this->created++;
                }
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }
    }

    protected function findExistingGuru($row, $kodeGuru, $email, $username)
    {
        $idGuru = trim((string) ($row['id_guru'] ?? $row['no_id'] ?? ''));
        if ($idGuru !== '') {
            $guru = Guru::find($idGuru);
            if ($guru) {
                return $guru;
            }
        }

        if (!empty($row['nip'])) {
            $guru = Guru::where('nip', $row['nip'])->first();
            if ($guru) {
                return $guru;
            }
        }

        if (!empty($kodeGuru)) {
            $guru = Guru::where('kode_guru', $kodeGuru)->first();
            if ($guru) {
                return $guru;
            }
        }

        if (!empty($email)) {
            $guru = Guru::where('email', $email)->first();
            if ($guru) {
                return $guru;
            }
        }

        if (!empty($username)) {
            $user = User::where('username', $username)->first();
            if ($user && $user->guru_id) {
                return Guru::find($user->guru_id);
            }
        }

        return null;
    }

    protected function normalizeJenisKelamin($value)
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string) $value);
        $prefix = strtoupper(substr($value, 0, 1));

        if ($prefix === 'L') {
            return 'L';
        }

        if ($prefix === 'P') {
            return 'P';
        }

        return null;
    }

    protected function createGuru($row, $jenisKelamin, $kodeGuru, $email, $username, $password, $roleGuru)
    {
        if (!empty($row['nip'])) {
            $existingNip = Guru::where('nip', $row['nip'])->first();
            if ($existingNip) {
                $this->pushError(0, 'NIP ' . $row['nip'] . ' sudah terdaftar.');
                return;
            }
        }

        if (!empty($kodeGuru)) {
            $existingKode = Guru::where('kode_guru', $kodeGuru)->first();
            if ($existingKode) {
                $this->pushError(0, 'Kode Guru ' . $kodeGuru . ' sudah terdaftar.');
                return;
            }
        }

        $guru = Guru::create([
            'nama' => $row['nama'],
            'nip' => $row['nip'] ?? null,
            'kode_guru' => $kodeGuru,
            'pangkat_golongan' => $row['pangkat_golongan'] ?? null,
            'email' => $email,
            'telepon' => $row['telepon'] ?? null,
            'alamat' => $row['alamat'] ?? null,
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'jenis_kelamin' => $jenisKelamin,
        ]);

        User::create([
            'name' => $row['nama'],
            'username' => $username,
            'password' => Hash::make($password),
            'email' => $email,
            'jenis_kelamin' => $jenisKelamin,
            'role_id' => $roleGuru->id,
            'guru_id' => $guru->id,
            'is_active' => true,
        ]);
    }

    protected function updateGuru($guru, $row, $jenisKelamin, $email, $username, $password, $roleGuru)
    {
        $guru->update([
            'nama' => $row['nama'],
            'nip' => $row['nip'] ?? $guru->nip,
            'kode_guru' => $row['kode_guru'] ?? $guru->kode_guru,
            'pangkat_golongan' => $row['pangkat_golongan'] ?? $guru->pangkat_golongan,
            'email' => $email,
            'telepon' => $row['telepon'] ?? $guru->telepon,
            'alamat' => $row['alamat'] ?? $guru->alamat,
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? $guru->tanggal_lahir),
            'jenis_kelamin' => $jenisKelamin,
        ]);

        $user = User::where('guru_id', $guru->id)->first();
        if ($user) {
            $updateData = [
                'name' => $row['nama'],
                'email' => $email,
                'jenis_kelamin' => $jenisKelamin,
            ];

            if (!empty($username) && $username !== $user->username) {
                $updateData['username'] = $username;
            }

            if (!empty($password)) {
                $updateData['password'] = Hash::make($password);
            }

            $user->update($updateData);
        } elseif (!empty($username) && !empty($password)) {
            User::create([
                'name' => $row['nama'],
                'username' => $username,
                'password' => Hash::make($password),
                'email' => $email,
                'jenis_kelamin' => $jenisKelamin,
                'role_id' => $roleGuru->id,
                'guru_id' => $guru->id,
                'is_active' => true,
            ]);
        }
    }

    protected function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y'];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    protected function pushError($row, $message)
    {
        $this->errors[] = "Baris {$row}: {$message}";
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getCreatedCount()
    {
        return $this->created;
    }

    public function getUpdatedCount()
    {
        return $this->updated;
    }
}
