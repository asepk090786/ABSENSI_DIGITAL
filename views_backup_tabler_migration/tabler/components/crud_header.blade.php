<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        @yield('page-header')
    </div>
    <div class="d-flex gap-2">
        @yield('actions')
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-modern">
            <i class="ti ti-arrow-left me-1"></i>Kembali
        </a>
    </div>
</div>