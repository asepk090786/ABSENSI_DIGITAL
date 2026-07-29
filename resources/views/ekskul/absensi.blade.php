@extends('layouts.app', ['pageSlug' => 'ekskul'])

@section('title', 'Absensi - ' . $ekskul->nama)

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title fw-semibold m-0">Absensi: {{ $ekskul->nama }}</h4>
                <a href="{{ route('ekskul.index') }}" class="btn btn-secondary btn-sm">Kembali</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="GET" class="row g-2 mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Pilih Agenda</label>
                        <select name="agenda" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Tanpa Agenda --</option>
                            @foreach($agendaList as $ag)
                                <option value="{{ $ag->id }}" {{ ($agendaId ?? '') == $ag->id ? 'selected' : '' }}>
                                    {{ $ag->judul }} ({{ \Carbon\Carbon::parse($ag->tanggal)->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Absensi</label>
                        <input type="date" name="tanggal_absensi" id="tanggalAbsensi" class="form-control" value="{{ request('tanggal_absensi', date('Y-m-d')) }}">
                    </div>
                    @if($agendaId)
                    <div class="col-md-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="form-text text-muted">Agenda: {{ $agenda->judul ?? '' }} ({{ $agenda->tanggal ?? '' }})</div>
                    </div>
                    @endif
                </form>

                <form method="POST" action="{{ route('ekskul.absensi.store', $ekskul->id) }}">
                    @csrf
                    <input type="hidden" name="tanggal" value="{{ request('tanggal_absensi', date('Y-m-d')) }}">
                    <input type="hidden" name="ekskul_agenda_id" value="{{ $agendaId ?? '' }}">

                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover">
                            <thead>
                                <tr><th>No</th><th>NIS</th><th>Nama Siswa</th><th>Kelas</th><th width="250">Status</th><th>Keterangan</th></tr>
                            </thead>
                            <tbody>
                            @forelse($siswa as $index => $s)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $s->siswa->nis ?? '-' }}</td>
                                    <td>{{ $s->siswa->nama ?? '-' }}</td>
                                    <td>{{ $s->siswa->kelas->nama_kelas ?? '-' }}</td>
                                    <td>
                                        @php
                                            $existingStatus = $existingAbsensi->get($s->siswa_id)->status ?? 'hadir';
                                        @endphp
                                        <select name="absensi[{{ $index }}][siswa_id]" hidden>
                                            <option value="{{ $s->siswa_id }}" selected></option>
                                        </select>
                                        <select name="absensi[{{ $index }}][status]" class="form-select form-select-sm">
                                            <option value="hadir" {{ $existingStatus === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="izin" {{ $existingStatus === 'izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="sakit" {{ $existingStatus === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="alpha" {{ $existingStatus === 'alpha' ? 'selected' : '' }}>Alpha</option>
                                            <option value="tanpa_keterangan" {{ $existingStatus === 'tanpa_keterangan' ? 'selected' : '' }}>Tanpa Keterangan</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="absensi[{{ $index }}][keterangan]" class="form-control form-control-sm"
                                               value="{{ $existingAbsensi->get($s->siswa_id)->keterangan ?? '' }}" maxlength="200">
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">Belum ada anggota yang diterima.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($siswa->isNotEmpty())
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan Absensi
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection