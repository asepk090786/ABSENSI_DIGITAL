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
        $roleGuru = Role::where('role_name', 'Guru')->first();
        
        if (!$roleGuru) {
            $this->pushError(0, 'Role Guru tidak ditemukan di database.');
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 karena header di row 1 dan index mulai dari 0

            try {
                // Validasi wajib
                $validator = Validator::make($row->toArray(), [
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

                // Normalisasi jenis kelamin
                $jenisKelamin = strtoupper(substr($row['jenis_kelamin'], 0, 1));
                
                // Validasi NIP jika ada
                if (!empty($row['nip'])) {
                    $existingGuru = Guru::where('nip', $row['nip'])->first();
                    if ($existingGuru) {
                        $this->pushError($rowNumber, 'NIP ' . $row['nip'] . ' sudah terdaftar.');
                        continue;
                    }
                }

                // Validasi Kode Guru jika ada
                if (!empty($row['kode_guru'])) {
                    $existingGuru = Guru::where('kode_guru', $row['kode_guru'])->first();
                    if ($existingGuru) {
                        $this->pushError($rowNumber, 'Kode Guru ' . $row['kode_guru'] . ' sudah terdaftar.');
                        continue;
                    }
                }

                // Buat data guru
                $guru = Guru::create([
                    'nama' => $row['nama'],
                    'nip' => $row['nip'] ?? null,
                    'kode_guru' => $row['kode_guru'] ?? null,
                    'email' => $row['email'],
                    'telepon' => $row['telepon'] ?? null,
                    'alamat' => $row['alamat'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'jenis_kelamin' => $jenisKelamin,
                ]);

                // Buat akun user
                User::create([
                    'name' => $row['nama'],
                    'username' => $row['username'],
                    'password' => Hash::make($row['password']),
                    'email' => $row['email'],
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
