@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Pengaturan')

@section('content')
    <h3>Pengaturan Sistem</h3>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <strong>Tahun Ajaran Aktif</strong>
                </div>
                <div class="card-body">
                    @if($active_tahun)
                        <p><strong>{{ $active_tahun->nama_tahun }}</strong></p>
                    @else
                        <p class="text-danger">Tidak ada tahun ajaran aktif</p>
                    @endif
                    <a href="{{ route('setting.tahun_ajaran') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <strong>Semester Aktif</strong>
                </div>
                <div class="card-body">
                    @if($active_semester)
                        <p><strong>{{ $active_semester->nama_semester }}</strong></p>
                    @else
                        <p class="text-danger">Tidak ada semester aktif</p>
                    @endif
                    <a href="{{ route('setting.semester') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <strong>Pengaturan Tampilan Jadwal</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('setting.jadwal_visibility.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tampilkan_jadwal" value="0">

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="tampilkan_jadwal" name="tampilkan_jadwal" value="1" {{ optional($sekolah)->tampilkan_jadwal !== false ? 'checked' : '' }}>
                            <label class="form-check-label" for="tampilkan_jadwal">Tampilkan jadwal pada akun guru</label>
                        </div>

                        @error('tampilkan_jadwal')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="mb-3">
                            <label for="jadwal_maintenance_message" class="form-label">Pesan notifikasi (ditampilkan saat jadwal dinonaktifkan)</label>
                            <textarea class="form-control" id="jadwal_maintenance_message" name="jadwal_maintenance_message" rows="3">{{ old('jadwal_maintenance_message', optional($sekolah)->jadwal_maintenance_message) }}</textarea>
                            <div class="form-text">Anda dapat memasukkan teks sederhana atau HTML singkat untuk menampilkan informasi tambahan kepada guru.</div>
                        </div>

                        <p class="text-muted small">
                            Jika dinonaktifkan, preview jadwal di akun guru akan diganti dengan informasi bahwa jadwal masih dalam proses perbaikan.
                        </p>

                        <button type="submit" class="btn btn-primary btn-sm">Simpan pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
