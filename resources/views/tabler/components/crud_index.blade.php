@extends('tabler.layouts.main')

@section('title', $title ?? ucfirst($module))

@section('page-header')
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h2 class="page-title mb-0">{{ $title ?? ucfirst($module) }}</h2>
        <p class="text-muted small m-0">Kelola data {{ strtolower($title ?? $module) }}</p>
    </div>
    <div>
        @yield('action-buttons')
    </div>
</div>
@endsection

@section('content')
<div class="card" style="border-radius:12px;">
    <div class="card-header bg-white py-3">
        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
            <div class="d-flex align-items-center gap-2 flex-fill" style="max-width:360px;">
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <i class="ti ti-search"></i>
                    </span>
                    <input type="search" class="form-control form-control-modern" id="search-table" placeholder="Cari {{ strtolower($title ?? $module) }}...">
                </div>
            </div>
            <div class="d-flex gap-2">
                <x-tabler.filters :params="$_GET" :defaults="['per_page' => 10, 'sort' => 'desc']" />
            </div>
        </div>
    </div>
    <div class="table-responsive-custom">
        <table class="table card-table table-vcenter table-hover" id="data-table">
            <thead>
                @yield('table-head')
            </thead>
            <tbody>
                @forelse($items as $item)
                @yield('table-row', \View::make('tabler.components.table_row', ['item' => $item, 'fields' => $fields ?? []]))
                @empty
                <tr>
                    <td colspan="{{ $fields->count() + 1 }}" class="text-center py-5 text-muted">
                        <i class="ti ti-inbox ti-3x d-block mb-2 opacity-50"></i>
                        Data {{ $module }} belum tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div class="card-footer bg-white py-3">
        <div class="d-flex align-items-center justify-content-between">
            <span class="text-muted small">Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} data</span>
            <div>
                {{ $items->links('tabler.components.pagination') }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection