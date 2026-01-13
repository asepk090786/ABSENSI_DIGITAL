@extends('layouts.app', ['pageSlug' => 'agenda'])

@section('title','Tambah Agenda Kelas')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Tambah Agenda Kelas</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('agenda_kelas.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label>Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas ?? 'Kelas '.$k->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Guru</label>
                        <select name="guru_id" class="form-select" required>
                            @foreach($guru as $g)
                                <option value="{{ $g->id }}">{{ $g->nama ?? 'Guru '.$g->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Jam KBM</label>
                        <select name="jam_belajar_id" class="form-select" required id="jamSelect">
                            <option value="">-- Pilih Jam KBM --</option>
                            @foreach($jam as $j)
                                <option value="{{ $j->id }}" data-hari="{{ $j->hari }}">
                                    {{ $j->hari }} - Jam Ke-{{ $j->urutan }} ({{ $j->jam_mulai }} - {{ $j->jam_selesai }} | {{ $j->jenis }})
                                </option>
                            @endforeach
                        </select>
                        @error('jam_belajar_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label>Kegiatan</label>
                        <textarea name="kegiatan" class="form-control"></textarea>
                    </div>

                    <button class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
