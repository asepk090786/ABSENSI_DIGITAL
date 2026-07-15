<a href="#" class="dropdown-toggle text-reset text-decoration-none" data-bs-toggle="dropdown">
    <span class="avatar avatar-sm" style="background:#206bc4;color:#fff;border-radius:50%;width:34px;height:34px;display:inline-flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;">
        {{ substr(auth()->user()?->name ?? 'U', 0, 1) }}
    </span>
</a>
<ul class="dropdown-menu dropdown-menu-end">
    <li>
        <div class="px-3 py-2">
            <div class="fw-semibold">{{ auth()->user()?->name ?? 'User' }}</div>
            <div class="text-muted small">{{ auth()->user()?->role?->nama_role ?? 'User' }}</div>
            @if(auth()->user()?->guru)
                <div class="text-muted small">{{ auth()->user()->guru->nama_guru }}</div>
            @endif
        </div>
    </li>
    <li><hr class="dropdown-divider m-0"></li>
    <li>
        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();document.getElementById('logout-desktop').submit();">
            <i class="ti ti-logout me-2 text-danger"></i>Keluar
        </a>
    </li>
</ul>
<form id="logout-desktop" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>