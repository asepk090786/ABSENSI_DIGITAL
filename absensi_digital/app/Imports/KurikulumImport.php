<?php

namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class KurikulumImport implements ToCollection, WithHeadingRow
{
    private string $tingkat;
    private ?string $jurusan;

    public function __construct(string $tingkat, ?string $jurusan)
    {
        $this->tingkat = $tingkat;
        $this->jurusan = $jurusan;
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function() use ($rows) {
            DB::table('kurikulum_mapel')
                ->where('tingkat', $this->tingkat)
                ->where('jurusan', $this->jurusan)
                ->delete();

            foreach ($rows as $row) {
                if (!$row || (!isset($row['nama_mapel']) && !isset($row['kode_mapel']))) {
                    continue;
                }

                $nama = trim((string)($row['nama_mapel'] ?? ''));
                $kode = trim((string)($row['kode_mapel'] ?? ''));
                $jp = (int) ($row['jp'] ?? 0);

                // Cari mapel berdasar kode jika ada, fallback ke nama
                $mapelId = DB::table('mata_pelajaran')
                    ->when($kode !== '', function($q) use ($kode) {
                        return $q->where('kode_mapel', $kode);
                    })
                    ->when($kode === '' && $nama !== '', function($q) use ($nama) {
                        return $q->where('nama_mapel', $nama);
                    })
                    ->value('id');

                if (!$mapelId) {
                    continue; // skip baris yang tidak dikenal
                }

                DB::table('kurikulum_mapel')->insert([
                    'tingkat' => $this->tingkat,
                    'jurusan' => $this->jurusan,
                    'mata_pelajaran_id' => $mapelId,
                    'jp' => $jp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
