@extends('layouts.app')

@section('title', 'Scan QR Siswa')

@section('content')
<style>
    #qrReader { min-height: 320px; overflow: hidden; }
    #qrReader video { width: 100% !important; border-radius: .5rem; }
</style>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
    <div>
        <h2 class="mb-1"><i class="ti ti-qrcode me-2 text-primary"></i>Scan QR Siswa</h2>
        <p class="text-muted mb-0">Scan QR pada kartu login siswa untuk mencatat kehadiran pada jadwal Anda.</p>
    </div>
    <a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="ti ti-arrow-left me-1"></i>Kembali</a>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">Pemindai Kamera</h3>
                <span class="badge bg-blue-lt" id="scannerState">Siap</span>
            </div>
            <div class="card-body">
                <div id="qrReader" class="rounded border bg-light"></div>
                <div id="scanMessage" class="alert alert-secondary mt-3 mb-0" role="status">Arahkan kamera ke QR code kartu login siswa.</div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header"><h3 class="card-title mb-0">Jadwal Hari Ini</h3></div>
            <div class="list-group list-group-flush">
                @forelse($schedules as $schedule)
                    <div class="list-group-item d-flex justify-content-between align-items-center {{ $currentSchedule?->id === $schedule->id ? 'bg-green-lt' : '' }}">
                        <div>
                            <div class="fw-semibold">{{ $schedule->kelas->nama_kelas ?? 'Kelas' }}</div>
                            <div class="text-muted small">{{ $schedule->jamBelajar->jam_mulai ?? '-' }} - {{ $schedule->jamBelajar->jam_selesai ?? '-' }}</div>
                        </div>
                        <span class="badge {{ $currentSchedule?->id === $schedule->id ? 'bg-green' : 'bg-secondary-lt' }}">Jam {{ $schedule->jamBelajar->urutan ?? '-' }}</span>
                    </div>
                @empty
                    <div class="list-group-item text-muted">Tidak ada jadwal mengajar hari ini.</div>
                @endforelse
            </div>
        </div>
        <div class="alert alert-info mb-0">
            <i class="ti ti-info-circle me-2"></i>Absensi hanya dibuat untuk jadwal yang sedang berlangsung. QR kartu login siswa tetap menggunakan token yang sama.
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const state = document.getElementById('scannerState');
        const message = document.getElementById('scanMessage');
        let handled = false;
        const scanner = new Html5Qrcode('qrReader');

        function setMessage(text, type) {
            message.className = 'alert alert-' + type + ' mt-3 mb-0';
            message.textContent = text;
        }

        function stopScanner() {
            return scanner.stop().catch(function () {});
        }

        function scanSuccess(decodedText) {
            if (handled) return;
            handled = true;
            state.textContent = 'Memproses';
            setMessage('QR terbaca. Menyimpan absensi...', 'warning');
            stopScanner().then(function () {
                fetch('{{ route('absensi.qr.scan') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ qr_text: decodedText })
                })
                .then(function (response) { return response.json().then(function (data) { return { response: response, data: data }; }); })
                .then(function (result) {
                    if (!result.response.ok || !result.data.success) throw new Error(result.data.message || 'Absensi gagal disimpan.');
                    state.textContent = 'Berhasil';
                    setMessage(result.data.message, 'success');
                    setTimeout(function () { handled = false; state.textContent = 'Siap'; scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, scanSuccess, function () {}); }, 1500);
                })
                .catch(function (error) {
                    state.textContent = 'Coba lagi';
                    setMessage(error.message, 'danger');
                    setTimeout(function () { handled = false; scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, scanSuccess, function () {}); }, 1500);
                });
            });
        }

        state.textContent = 'Meminta kamera';
        scanner.start({ facingMode: 'environment' }, { fps: 10, qrbox: { width: 240, height: 240 } }, scanSuccess, function () {})
            .then(function () { state.textContent = 'Aktif'; })
            .catch(function () { state.textContent = 'Kamera gagal'; setMessage('Kamera tidak dapat digunakan. Izinkan akses kamera lalu muat ulang halaman.', 'danger'); });
    });
</script>
@endpush
