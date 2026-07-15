@extends('tabler.layouts.main')

@php
$activeKelas = \App\Models\Kelas::where('status', '!=', 'nonaktif')->count();
$stats = [
    ['label' => 'Total Siswa', 'value' => \App\Models\Siswa::where('status', '!=', 'nonaktif')->count(), 'icon' => 'ti-user', 'color' => 'blue'],
    ['label' => 'Total Guru', 'value' => \App\Models\Guru::count(), 'icon' => 'ti-user-check', 'color' => 'teal'],
    ['label' => 'Total Kelas', 'value' => $activeKelas, 'icon' => 'ti-school', 'color' => 'violet'],
    ['label' => 'Mata Pelajaran', 'value' => \App\Models\MataPelajaran::count(), 'icon' => 'ti-book', 'color' => 'orange'],
];

$quickMenus = [
    ['label' => 'Siswa', 'icon' => 'ti-user', 'route' => route('siswa.index'), 'color' => 'blue'],
    ['label' => 'Guru', 'icon' => 'ti-user-check', 'route' => route('guru.index'), 'color' => 'teal'],
    ['label' => 'Nilai', 'icon' => 'ti-file-text', 'route' => route('nilai.index'), 'color' => 'violet'],
    ['label' => 'Jadwal', 'icon' => 'ti-calendar', 'route' => route('jadwal-kbm.index'), 'color' => 'orange'],
];
@endphp

@section('title', 'Dashboard')

@section('page-header')
<div class="page-header">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2 class="page-title mb-0">Dashboard</h2>
            <p class="text-muted small m-0">Selamat datang, <strong>{{ auth()->user()?->name ?? 'User' }}</strong></p>
        </div>
        <div class="text-muted small">{{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</div>
    </div>
</div>
@endsection

@section('content')
<div class="row g-2 mb-2">
    @foreach($stats as $stat)
    <div class="col-6 col-md-3">
        <div class="card card-sm h-100" style="border-radius:10px;">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="stat-icon bg-{{ $stat['color'] }}-lt text-{{ $stat['color'] }}">
                    <i class="ti {{ $stat['icon'] }}"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">{{ $stat['label'] }}</div>
                    <div class="display-6 fw-bold m-0">{{ number_format($stat['value']) }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-2 mb-2">
    <div class="col-12 col-lg-8">
        <div class="card" style="border-radius:10px;">
            <div class="card-header bg-white py-3">
                <h3 class="card-title m-0">Menu Cepat</h3>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($quickMenus as $menu)
                    <div class="col-6 col-md-3">
                        <a href="{{ $menu['route'] }}" class="quick-menu-card d-block text-center p-3 text-decoration-none text-reset">
                            <div class="stat-icon bg-{{ $menu['color'] }}-lt text-{{ $menu['color'] }} mx-auto mb-2">
                                <i class="ti {{ $menu['icon'] }}"></i>
                            </div>
                            <div class="fw-medium small">{{ $menu['label'] }}</div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card h-100" style="border-radius:10px;">
            <div class="card-header bg-white py-3">
                <h3 class="card-title m-0">Aktivitas Terbaru</h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-item-marker bg-blue-lt text-blue">
                            <i class="ti ti-user-plus"></i>
                        </div>
                        <div class="timeline-item-content">
                            <p class="mb-0">Data siswa ditambahkan hari ini</p>
                            <span class="text-muted small">Baru saja</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-item-marker bg-teal-lt text-teal">
                            <i class="ti ti-calendar-stats"></i>
                        </div>
                        <div class="timeline-item-content">
                            <p class="mb-0">Jadwal KBM tersedia untuk semester ini</p>
                            <span class="text-muted small">2 jam lalu</span>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-item-marker bg-orange-lt text-orange">
                            <i class="ti ti-file-text"></i>
                        </div>
                        <div class="timeline-item-content">
                            <p class="mb-0">Nilai terbaru telah diinput</p>
                            <span class="text-muted small">5 jam lalu</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection