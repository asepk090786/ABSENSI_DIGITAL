@extends('layouts.app')

@section('title','Agenda Kelas')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Agenda Kelas</h3>
        <a href="{{ route('agenda_kelas.create') }}" class="btn btn-primary">Tambah Agenda</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <table class="table table-sm">
        <thead><tr><th>Tanggal</th><th>Kelas</th><th>Guru</th><th>Jam</th><th>Kegiatan</th></tr></thead>
        <tbody>
        @foreach($items as $it)
            <tr>
                <td>{{ $it->tanggal }}</td>
                <td>{{ DB::table('kelas')->where('id',$it->kelas_id)->value('nama_kelas') }}</td>
                <td>{{ DB::table('guru')->where('id',$it->guru_id)->value('nama') }}</td>
                <td>{{ DB::table('jam_belajar')->where('id',$it->jam_belajar_id)->value('jam_mulai') }} - {{ DB::table('jam_belajar')->where('id',$it->jam_belajar_id)->value('jam_selesai') }}</td>
                <td>{{ $it->kegiatan }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
