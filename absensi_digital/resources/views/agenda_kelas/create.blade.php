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
                        <label>Jam Belajar</label>
                        <select name="jam_belajar_id" class="form-select" required>
                            @foreach($jam as $j)
                                <option value="{{ $j->id }}">{{ $j->hari }} {{ \Carbon\Carbon::parse($j->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($j->jam_selesai)->format('H:i') }} ({{ $j->jenis }})</option>
                            @endforeach
                        </select>
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
