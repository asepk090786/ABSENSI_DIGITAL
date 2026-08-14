<?php

namespace App\Imports;

use App\Models\TenagaPendidikan;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TenagaPendidikanImport implements ToCollection, WithHeadingRow
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
        $roleTenagaPendidikan = Role::where('role_name', 'Tenaga Pendidikan')->first();

        if (!$roleTenagaPendidikan) {
            $this->pushError(0, 'Role Tenaga Pendidikan tidak ditemukan di database.');
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $jenisKelamin = $this->normalizeJenisKelamin($row['jenis_kelamin'] ?? null);
                $nip = trim((string) ($row['nip'] ?? '')) ?: null;
                $email = trim((string) ($row['email'] ?? ''));
                if (empty($email) && !empty($nip)) {
                    $email = 'tp' . $nip . '@simadis.sch.id';
                }

                $username = trim((string) ($row['username'] ?? ''));
                if (empty($username) && !empty($nip)) {
                    $username = 'tp' . $nip;
                }
                $password = trim((string) ($row['password'] ?? ''));
                if (empty($password)) {
                    $password = $username ?: 'password123';
                }

                $existingTenagaPendidikan = $this->findExistingTenagaPendidikan($row, $nip, $email, $username);

                if ($this->mode === 'update') {
                    if (!$existingTenagaPendidikan) {
                        $this->pushError($rowNumber, 'Data tenaga pendidikan tidak ditemukan. Gunakan kolom id_tenaga_pendidikan, NIP, email, atau username yang sesuai untuk update.');
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

                    $this->updateTenagaPendidikan($existingTenagaPendidikan, $row, $jenisKelamin, $email, $username, $password, $roleTenagaPendidikan);
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

                if (User::where('username', $username)->exists()) {
                    $this->pushError($rowNumber, "Username '{$username}' sudah digunakan.");
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    $this->pushError($rowNumber, "Email '{$email}' sudah digunakan.");
                    continue;
                }

                if (TenagaPendidikan::where('email', $email)->exists()) {
                    $this->pushError($rowNumber, "Email '{$email}' sudah terdaftar.");
                    continue;
                }

                if (TenagaPendidikan::where('nip', $nip)->exists()) {
                    $this->pushError($rowNumber, "NIP '{$nip}' sudah terdaftar.");
                    continue;
                }

                $tenagaPendidikan = TenagaPendidikan::create([
                    'nama' => $row['nama'],
                    'nip' => $nip,
                    'jabatan' => $row['jabatan'] ?? null,
                    'email' => $email,
                    'telepon' => $row['telepon'] ?? null,
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'jenis_kelamin' => $jenisKelamin,
                    'alamat' => $row['alamat'] ?? null,
                ]);

                User::create([
                    'name' => $row['nama'],
                    'username' => $username,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'jenis_kelamin' => $jenisKelamin,
                    'role_id' => $roleTenagaPendidikan->id,
                    'tenaga_pendidikan_id' => $tenagaPendidikan->id,
                    'is_active' => 1,
                ]);

                $this->created++;
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }
    }

    private function normalizeJenisKelamin($value): ?string
    {
        if ($value === null) return null;
        $value = strtoupper(trim((string) $value));
        if ($value === 'L' || $value === 'LAKI-LAKI' || $value === 'LAKI') return 'L';
        if ($value === 'P' || $value === 'PEREMPUAN' || $value === 'WANITA') return 'P';
        return null;
    }

    private function parseDate($dateValue)
    {
        if (!$dateValue) return null;

        if (is_numeric($dateValue)) {
            // Excel serial number
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue);
        }

        try {
            return \Carbon\Carbon::parse($dateValue);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function findExistingTenagaPendidikan($row, $nip, $email, $username): ?TenagaPendidikan
    {
        $id = $row['id_tenaga_pendidikan'] ?? null;

        if (!empty($id)) {
            $tenaga = TenagaPendidikan::find($id);
            if ($tenaga) return $tenaga;
        }

        if (!empty($nip)) {
            $tenaga = TenagaPendidikan::where('nip', $nip)->first();
            if ($tenaga) return $tenaga;
        }

        if (!empty($email)) {
            $tenaga = TenagaPendidikan::where('email', $email)->first();
            if ($tenaga) return $tenaga;
        }

        if (!empty($username)) {
            $user = User::where('username', $username)->first();
            if ($user && $user->tenaga_pendidikan_id) {
                return TenagaPendidikan::find($user->tenaga_pendidikan_id);
            }
        }

        return null;
    }

    private function updateTenagaPendidikan($tenagaPendidikan, $row, $jenisKelamin, $email, $username, $password, $role)
    {
        $tenagaPendidikan->update([
            'nama' => $row['nama'] ?? $tenagaPendidikan->nama,
            'nip' => $row['nip'] ?? $tenagaPendidikan->nip,
            'jabatan' => $row['jabatan'] ?? $tenagaPendidikan->jabatan,
            'email' => $email ?? $tenagaPendidikan->email,
            'telepon' => $row['telepon'] ?? $tenagaPendidikan->telepon,
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null) ?? $tenagaPendidikan->tanggal_lahir,
            'jenis_kelamin' => $jenisKelamin ?? $tenagaPendidikan->jenis_kelamin,
            'alamat' => $row['alamat'] ?? $tenagaPendidikan->alamat,
        ]);

        if ($tenagaPendidikan->user) {
            $tenagaPendidikan->user->update([
                'name' => $row['nama'] ?? $tenagaPendidikan->user->name,
                'username' => $username ?? $tenagaPendidikan->user->username,
                'email' => $email ?? $tenagaPendidikan->user->email,
                'jenis_kelamin' => $jenisKelamin ?? $tenagaPendidikan->user->jenis_kelamin,
            ]);

            if (!empty($password) && $password !== trim((string) ($row['password'] ?? ''))) {
                $tenagaPendidikan->user->update(['password' => Hash::make($password)]);
            }
        }
    }

    private function pushError($rowNumber, $message)
    {
        $this->errors[$rowNumber] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }
}
