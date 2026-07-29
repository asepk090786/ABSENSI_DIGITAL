<?php

namespace App\Services;

use App\Models\AgendaGuru;
use App\Models\AgendaKelas;
use Illuminate\Support\Facades\DB;

class AgendaKelasStorageService
{
    public function syncAgendaGuru(AgendaKelas $agendaKelas): void
    {
        $kelas = DB::table('kelas')->find($agendaKelas->kelas_id);
        $kegiatanRingkasan = ($kelas ? $kelas->nama_kelas . ' - ' : '') . ($agendaKelas->kegiatan ?? $agendaKelas->nama_kegiatan ?? '');
        $kegiatanRingkasan = trim($kegiatanRingkasan);

        $agendaGuru = AgendaGuru::updateOrCreate(
            [
                'guru_id' => $agendaKelas->guru_id,
                'jam_belajar_id' => $agendaKelas->jam_belajar_id,
                'tanggal' => $agendaKelas->tanggal,
                'tahun_ajaran_id' => $agendaKelas->tahun_ajaran_id,
                'semester_id' => $agendaKelas->semester_id,
            ],
            [
                'kegiatan' => $this->mergeAgendaKegiatan($agendaKelas, $kegiatanRingkasan),
            ]
        );
    }

    private function mergeAgendaKegiatan(AgendaKelas $agendaKelas, string $newKegiatan): string
    {
        $agendaGuru = AgendaGuru::where('guru_id', $agendaKelas->guru_id)
            ->where('jam_belajar_id', $agendaKelas->jam_belajar_id)
            ->where('tanggal', $agendaKelas->tanggal)
            ->where('tahun_ajaran_id', $agendaKelas->tahun_ajaran_id)
            ->where('semester_id', $agendaKelas->semester_id)
            ->first();

        if (!$agendaGuru || $agendaGuru->kegiatan === null || trim($agendaGuru->kegiatan) === '') {
            return $newKegiatan;
        }

        if ($newKegiatan === '' || strpos($agendaGuru->kegiatan, $newKegiatan) !== false) {
            return $agendaGuru->kegiatan;
        }

        return trim($agendaGuru->kegiatan . "\n" . $newKegiatan);
    }

    public function cleanupAgendaGuru(AgendaKelas $deletedAgenda): void
    {
        $otherAgendas = AgendaKelas::where('guru_id', $deletedAgenda->guru_id)
            ->where('jam_belajar_id', $deletedAgenda->jam_belajar_id)
            ->where('tanggal', $deletedAgenda->tanggal)
            ->where('tahun_ajaran_id', $deletedAgenda->tahun_ajaran_id)
            ->where('semester_id', $deletedAgenda->semester_id)
            ->where('id', '!=', $deletedAgenda->id)
            ->count();

        if ($otherAgendas === 0) {
            AgendaGuru::where('guru_id', $deletedAgenda->guru_id)
                ->where('jam_belajar_id', $deletedAgenda->jam_belajar_id)
                ->where('tanggal', $deletedAgenda->tanggal)
                ->where('tahun_ajaran_id', $deletedAgenda->tahun_ajaran_id)
                ->where('semester_id', $deletedAgenda->semester_id)
                ->delete();
        }
    }
}
