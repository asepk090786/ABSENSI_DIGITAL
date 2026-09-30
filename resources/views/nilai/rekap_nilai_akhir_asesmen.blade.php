@extends('layouts.app')

@section('title', 'Rekap Nilai Akhir')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title"><i class="ti ti-report-analytics me-2"></i>Rekap Nilai Akhir</h2>
                <div class="text-muted mt-1">{{ $kelas->nama_kelas }} &middot; {{ $mapel->nama_mapel }}</div>
                @if($tahunAjaranAktif && $semesterAktif)
                    <div class="text-muted small mt-2"><i class="ti ti-calendar me-1"></i>{{ $tahunAjaranAktif->tahun_ajaran }} - <strong>{{ $semesterAktif->nama_semester }}</strong></div>
                @endif
            </div>
            <div class="col-auto ms-auto d-print-none d-flex flex-wrap gap-2">
                @if($previewMode)
                    <a href="{{ route('nilai.rekap.akhir-asesmen.export', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'format' => $exportFormat]) }}" class="btn btn-primary"><i class="ti ti-download me-1"></i>Unduh {{ strtoupper($exportFormat) }}</a>
                    <a href="{{ route('nilai.rekap.akhir-asesmen', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]) }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali ke Rekap</a>
                @else
                    <a href="{{ route('nilai.rekap.akhir-asesmen.preview', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'format' => 'excel']) }}" class="btn btn-outline-success"><i class="ti ti-file-spreadsheet me-1"></i>Pratinjau Excel</a>
                    <a href="{{ route('nilai.rekap.akhir-asesmen.preview', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id, 'format' => 'pdf']) }}" class="btn btn-outline-danger"><i class="ti ti-file-type-pdf me-1"></i>Pratinjau PDF</a>
                    <a href="{{ route('nilai.index', ['kelas_id' => $kelas->id, 'mapel_id' => $mapel->id]) }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="page-body">
    <div class="container-xl">
        @if($previewMode)
            <div class="alert alert-info" role="status">
                <i class="ti ti-eye me-2"></i>Pratinjau file {{ strtoupper($exportFormat) }}. Periksa bobot dan nilai siswa, lalu pilih Unduh untuk membuat file.
            </div>
        @endif

        <div class="row row-cards mb-3">
            <div class="col-sm-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="subheader">Bobot Formatif</div><div class="h2 mb-0">{{ number_format($data['bobot_formatif'], 2) }}%</div></div></div></div>
            <div class="col-sm-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="subheader">Bobot Sumatif</div><div class="h2 mb-0">{{ number_format($data['bobot_sumatif'], 2) }}%</div></div></div></div>
            <div class="col-sm-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="subheader">Rata-rata Formatif</div><div class="h2 mb-0">{{ $data['rata_formatif'] !== null ? number_format($data['rata_formatif'], 2) : '-' }}</div></div></div></div>
            <div class="col-sm-6 col-lg-3"><div class="card card-sm"><div class="card-body"><div class="subheader">Rata-rata Sumatif</div><div class="h2 mb-0">{{ $data['rata_sumatif'] !== null ? number_format($data['rata_sumatif'], 2) : '-' }}</div></div></div></div>
        </div>

        <div class="card">
            <div class="card-header"><h3 class="card-title">Nilai Akhir per Siswa</h3><div class="card-actions fw-semibold">Rata-rata kelas: {{ $data['nilai_akhir'] !== null ? number_format($data['nilai_akhir'], 2) : '-' }}</div></div>
            <div class="table-responsive">
                <table class="table table-vcenter table-striped table-hover">
                    <thead><tr><th>No</th><th>Nama Siswa</th><th class="text-center">Rata-rata Formatif</th><th class="text-center">Rata-rata Sumatif</th><th class="text-center">Nilai Akhir</th><th class="text-center">Keterangan</th></tr></thead>
                    <tbody>
                        @forelse($data['students'] as $index => $student)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $student->nama }}</td>
                                <td class="text-center">{{ $student->rata_formatif !== null ? number_format($student->rata_formatif, 2) : '-' }}</td>
                                <td class="text-center">{{ $student->rata_sumatif !== null ? number_format($student->rata_sumatif, 2) : '-' }}</td>
                                <td class="text-center fw-bold">{{ $student->nilai_akhir !== null ? number_format($student->nilai_akhir, 2) : '-' }}</td>
                                <td class="text-center">{{ $student->nilai_akhir !== null ? 'Lengkap' : 'Nilai formatif atau sumatif belum tersedia' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada siswa pada kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection