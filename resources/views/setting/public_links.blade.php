@extends('layouts.app')

@section('title', 'Link Publik')

@section('content')
<div class="container-fluid">
    <div class="mb-4"><h3 class="mb-1">Link Publik</h3><p class="text-muted mb-0">Bagikan informasi absensi dan agenda tanpa login.</p></div>
    <div class="row g-3">
        @foreach([
            ['title' => 'Rekap Absensi Hari Ini', 'description' => 'Rekap absen per kelas, detail siswa, dan daftar guru.', 'url' => route('public.rekap-absensi'), 'icon' => 'ti-checkup-list'],
            ['title' => 'Agenda Kelas Semua Kelas', 'description' => 'Agenda kelas aktif untuk seluruh kelas dan guru.', 'url' => route('public.agenda-kelas'), 'icon' => 'ti-calendar-event'],
            ['title' => 'Rekap Kehadiran Guru', 'description' => 'Status kehadiran seluruh guru berdasarkan tanggal.', 'url' => route('public.rekap-absensi-guru'), 'icon' => 'ti-user-check'],
            ['title' => 'Jadwal Pembelajaran Daring', 'description' => 'Jadwal KBM aktif per kelas dengan nama guru, mata pelajaran, dan link Zoom/Meet/lainnya.', 'url' => route('public.jadwal-pembelajaran-daring'), 'icon' => 'ti-video'],
        ] as $link)
            <div class="col-12 col-lg-6"><div class="card h-100"><div class="card-body">
                <h5><i class="ti {{ $link['icon'] }} me-2 text-primary"></i>{{ $link['title'] }}</h5>
                <p class="text-muted">{{ $link['description'] }}</p>
                <div class="input-group"><input class="form-control" value="{{ $link['url'] }}" readonly><button class="btn btn-primary" type="button" onclick="navigator.clipboard.writeText('{{ $link['url'] }}')">Salin</button><a class="btn btn-outline-secondary" href="{{ $link['url'] }}" target="_blank" rel="noopener">Buka</a></div>
            </div></div></div>
        @endforeach
    </div>
</div>
@endsection