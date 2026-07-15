@extends('layouts.app', ['pageSlug' => 'ekstrakurikuler'])

@section('title','Ekstrakurikuler')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Ekstrakurikuler</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="ti ti-arrow-left me-1"></i>Back
                            </a>
                            <a href="{{ route('ekstrakurikuler.create') }}" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Ekstrakurikuler
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i>{{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Ekstrakurikuler</th>
                                <th>Deskripsi</th>
                                <th>Pembina</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($items as $index => $it)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $it->nama_ekskul }}</td>
                                <td>{{ $it->deskripsi ?? '-' }}</td>
                                <td>{{ $it->pembina->nama ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data ekstrakurikuler.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
