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
            $rowNumber = $index + 2; // +2 karena header di row 1 dan index mulai dari 0

            try {
                // Normalisasi jenis kelamin
                $jenisKelamin = strtoupper(substr($row['jenis_kelamin'] ?? '', 0, 1));

                // Siapkan kode guru
                $kodeGuru = $row['kode_guru'] ?? null;

                // Email default jika kosong
                $email = $row['email'] ?? null;
                if (empty($email) && !empty($kodeGuru)) {
                    $email = 'guru' . $kodeGuru . '@simadis.sch';
                }

                // Username dan password default jika kosong
                $username = $row['username'] ?? null;
                if (empty($username) && !empty($kodeGuru)) {
                    $username = 'guru' . $kodeGuru;
                }
                $password = $row['password'] ?? null;
                if (empty($password) && !empty($kodeGuru)) {
                    $password = 'guru' . $kodeGuru;
                }

                // Validasi wajib (email, username, password bisa default)
                $validator = Validator::make([
                    'nama' => $row['nama'] ?? null,
                    'jenis_kelamin' => $jenisKelamin,
                    'username' => $username,
                    'password' => $password,
                    'email' => $email,
                ], [
                    'nama' => 'required|string|max:255',
                    'jenis_kelamin' => 'required|in:L,P,Laki-laki,Perempuan',
                    'username' => 'required|string|max:255|unique:users,username',
                    'password' => 'required|string|min:6',
                    'email' => 'required|email|max:255|unique:guru,email',
                ]);

                if ($validator->fails()) {
                    $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                    continue;
                }

                // Validasi NIP jika ada
                if (!empty($row['nip'])) {
                    $existingGuru = Guru::where('nip', $row['nip'])->first();
                    if ($existingGuru) {
                        $this->pushError($rowNumber, 'NIP ' . $row['nip'] . ' sudah terdaftar.');
                        continue;
                    }
                }

                // Validasi Kode Guru jika ada
                if (!empty($kodeGuru)) {
                    $existingGuru = Guru::where('kode_guru', $kodeGuru)->first();
                    if ($existingGuru) {
                        $this->pushError($rowNumber, 'Kode Guru ' . $kodeGuru . ' sudah terdaftar.');
                        continue;
                    }
                }

                // Buat data guru
                $guru = Guru::create([
                    'nama' => $row['nama'],
                    'nip' => $row['nip'] ?? null,
                    'kode_guru' => $kodeGuru,
                    'email' => $email,
                    'telepon' => $row['telepon'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'jenis_kelamin' => $jenisKelamin,
                ]);

                // Buat akun user
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

            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }
    }

    protected function parseDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Coba berbagai format
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
}
