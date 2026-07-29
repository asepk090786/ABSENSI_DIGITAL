@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Absensi Pembina - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title fw-semibold m-0">Check-in Pembina: {{ $ekskul->nama }}</h4>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($checkinToday)
                    <div class="alert alert-success">
                        <i class="ti ti-check-circle me-2"></i>Anda sudah check-in hari ini pada pukul {{ substr($checkinToday->jam_checkin, 0, 5) }}.
                    </div>
                @else
                    <form method="POST" action="{{ route('ekskul.absensi_pembina.store', $ekskul->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Foto Selfie</label>
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <div class="form-text">Opsional: Upload foto selfie sebagai bukti kehadiran.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokasi GPS</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" name="latitude" class="form-control" placeholder="Latitude" readonly>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" name="longitude" class="form-control" placeholder="Longitude" readonly>
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-info btn-sm mt-2" onclick="getLocation()">
                                <i class="ti ti-map-pin me-1"></i>Ambil Lokasi
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="ti ti-fingerprint me-1"></i>Check-in Sekarang
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Riwayat Check-in</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-vcenter">
                        <thead><tr><th>Tanggal</th><th>Jam</th><th>Foto</th><th>Lokasi</th></tr></thead>
                        <tbody>
                        @forelse($riwayat as $r)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') }}</td>
                                <td>{{ substr($r->jam_checkin, 0, 5) }}</td>
                                <td>@if($r->foto)<a href="{{ asset('storage/' . $r->foto) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="ti ti-photo"></i></a>@else - @endif</td>
                                <td>@if($r->latitude && $r->longitude){{ number_format($r->latitude, 5) }}, {{ number_format($r->longitude, 5) }}@else - @endif</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Belum ada riwayat check-in.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
function getLocation() {
    if (!navigator.geolocation) {
        alert('Geolokasi tidak didukung browser Anda.');
        return;
    }
    navigator.geolocation.getCurrentPosition(function(pos) {
        document.querySelector('input[name="latitude"]').value = pos.coords.latitude;
        document.querySelector('input[name="longitude"]').value = pos.coords.longitude;
    }, function() {
        alert('Gagal mendapatkan lokasi. Pastikan GPS aktif.');
    });
}
</script>
@endpush