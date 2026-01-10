@extends('layouts.app')

@section('title','Detail Tahun Ajaran')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">{{ $tahunAjaran->nama_tahun }}</h4>
        <a href="{{ route('setting.tahun_ajaran.edit', $tahunAjaran->id) }}" class="btn btn-warning btn-sm">Edit</a>
      </div>
      <div class="card-body">
        <p>Status: @if($tahunAjaran->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Tidak Aktif</span>@endif</p>
        <h5 class="mt-3">Semester</h5>
        <ul class="list-group">
          @forelse($semesters as $s)
            <li class="list-group-item d-flex justify-content-between align-items-center">
              {{ $s->nama_semester }}
              @if($s->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Tidak Aktif</span>@endif
            </li>
          @empty
            <li class="list-group-item text-muted">Belum ada semester</li>
          @endforelse
        </ul>
      </div>
    </div>
  </div>
</div>
@endsection
