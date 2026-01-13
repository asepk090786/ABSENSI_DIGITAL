@extends('layouts.app')

@section('title', 'Print Jadwal Kelas')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="ti ti-printer me-2"></i>Print Jadwal
        </button>
        <a href="{{ route('jadwal-kbm.create-by-kelas', $kelas->id) }}" class="btn btn-secondary">
            <i class="ti ti-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Print Content -->
<div id="printContent" class="card">
    <div class="card-body" style="background: white; padding: 40px;">
        <!-- Header -->
        <div style="text-align: center; margin-bottom: 30px;">
            @if($sekolah && $sekolah->logo && file_exists(public_path('storage/' . $sekolah->logo)))
                <img src="{{ asset('storage/' . $sekolah->logo) }}" style="height: 60px; margin-bottom: 10px;">
            @endif
            <h3 style="margin: 5px 0;">{{ $sekolah->nama_sekolah ?? 'Sekolah' }}</h3>
            <p style="margin: 2px 0; font-size: 12px;">{{ $sekolah->alamat ?? '' }}</p>
            <hr style="margin: 15px 0;">
        </div>

        <!-- Judul Jadwal -->
        <div style="text-align: center; margin-bottom: 20px;">
            <h4 style="margin: 10px 0; font-weight: bold;">JADWAL PELAJARAN KELAS {{ strtoupper($kelas->nama_kelas) }}</h4>
            <p style="margin: 5px 0; font-size: 12px;">
                Tahun Ajaran: {{ $tahunAjaran->nama_tahun ?? '-' }} | 
                Semester: {{ $semester->nama_semester ?? '-' }}
            </p>
        </div>

        <!-- Tabel Jadwal -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
            <thead>
                <tr style="background: #f0f0f0; border-bottom: 2px solid #333;">
                    <th style="border: 1px solid #333; padding: 8px; text-align: center;">Jam Ke</th>
                    <th style="border: 1px solid #333; padding: 8px; text-align: center;">Hari</th>
                    <th style="border: 1px solid #333; padding: 8px; text-align: left;">Mata Pelajaran</th>
                    <th style="border: 1px solid #333; padding: 8px; text-align: center;">Guru</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwalSorted as $jadwal)
                    <tr style="border-bottom: 1px solid #ddd;">
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $jadwal->jam_ke }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $jadwal->hari }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px;">{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                        <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $jadwal->guru->nip ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="border: 1px solid #ddd; padding: 12px; text-align: center; color: #999;">
                            Belum ada jadwal untuk kelas ini
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Keterangan Guru -->
        <div style="margin-top: 30px;">
            <h5 style="margin-bottom: 12px; font-weight: bold;">DAFTAR GURU PENGAJAR</h5>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0f0f0; border-bottom: 2px solid #333;">
                        <th style="border: 1px solid #333; padding: 8px; text-align: center;">No</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: center;">Kode Guru</th>
                        <th style="border: 1px solid #333; padding: 8px; text-align: left;">Nama Guru</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($guruList as $index => $guru)
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px; text-align: center;">{{ $guru->nip }}</td>
                            <td style="border: 1px solid #ddd; padding: 8px;">{{ $guru->nama }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="border: 1px solid #ddd; padding: 12px; text-align: center; color: #999;">
                                Tidak ada guru yang mengajar
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div style="margin-top: 40px;">
            <div style="text-align: right;">
                <p style="margin: 0;">Dicetak: {{ now()->format('d F Y H:i:s') }}</p>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body { margin: 0; padding: 0; }
        .btn, .row, .navbar-vertical, .page-header { display: none !important; }
        .card { box-shadow: none !important; border: none !important; }
        .card-body { padding: 0 !important; }
        table { page-break-inside: avoid; }
    }
</style>
@endsection
