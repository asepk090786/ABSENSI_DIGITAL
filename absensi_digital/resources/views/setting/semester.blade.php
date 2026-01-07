@extends('layouts.app')

@section('title','Semester')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Semester @if($active_tahun)({{ $active_tahun->nama_tahun }})@endif</h3>
        <a href="{{ route('setting.semester.create') }}" class="btn btn-primary">Tambah</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <table class="table table-sm">
        <thead>
            <tr><th>Semester</th><th>Tahun Ajaran</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        @forelse($semesters as $s)
            <tr>
                <td>{{ $s->nama_semester }}</td>
                <td>{{ optional($s->tahunAjaran)->nama_tahun ?? 'N/A' }}</td>
                <td>
                    @if($s->is_active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </td>
                <td>
                    @if(!$s->is_active)
                        <form action="{{ route('setting.semester.activate', $s->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Aktifkan</button>
                        </form>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Tidak ada semester</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
