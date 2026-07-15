@extends('layouts.app')

@section('title','Detail Semester')

@section('content')
<div class="row">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title fw-semibold m-0">{{ $semester->nama_semester }}</h4>
        <a href="{{ route('setting.semester.edit', $semester->id) }}" class="btn btn-warning btn-sm">Edit</a>
      </div>
      <div class="card-body">
        <p>Tahun Ajaran: {{ optional($semester->tahunAjaran)->nama_tahun ?? 'N/A' }}</p>
        <p>Status: @if($semester->is_active)<span class="badge bg-success">Aktif</span>@else<span class="badge bg-secondary">Tidak Aktif</span>@endif</p>
      </div>
    </div>
  </div>
</div>
@endsection
