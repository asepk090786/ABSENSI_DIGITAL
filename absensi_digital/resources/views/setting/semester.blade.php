@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Semester')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Semester @if($active_tahun)({{ $active_tahun->nama_tahun }})@endif</h4>
                <a href="{{ route('setting.semester.create') }}" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">
                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                <div class="table-responsive">
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
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
