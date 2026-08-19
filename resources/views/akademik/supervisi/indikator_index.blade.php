@extends('layouts.app', ['pageSlug' => 'supervisi'])
@section('title', 'Master Indikator Supervisi')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Master Indikator Supervisi</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('supervisi.dashboard') }}" class="btn btn-secondary btn-sm">Dashboard</a>
            <a href="{{ route('supervisi.indikator.create') }}" class="btn btn-primary btn-sm">+ Tambah Indikator</a>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted">Indikator observasi disusun agar data supervisi tetap terstruktur dan mudah ditindaklanjuti.</p>

        @if($items->isEmpty())
            <div class="alert alert-light border">
                Belum ada indikator. <a href="{{ route('supervisi.indikator.create') }}">Buat indikator baru</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Instrumen</th>
                            <th>Kategori</th>
                            <th>Indikator</th>
                            <th>Deskripsi</th>
                            <th style="width: 80px;">Bobot</th>
                            <th style="width: 100px;">Status</th>
                            <th style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td><strong>{{ $item->instrument->nama ?? '-' }}</strong></td>
                                <td><span class="badge bg-warning">{{ $item->kategori }}</span></td>
                                <td>{{ $item->indikator }}</td>
                                <td class="text-muted" style="max-width: 200px; overflow: hidden;">
                                    {{ Str::limit($item->deskripsi, 40) }}
                                </td>
                                <td><span class="badge bg-light text-dark">{{ $item->bobot }}</span></td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('supervisi.indikator.edit', $item) }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                            <span>Edit</span>
                                        </a>
                                        <form action="{{ route('supervisi.indikator.delete', $item) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Yakin hapus?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger d-inline-flex align-items-center gap-1" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
