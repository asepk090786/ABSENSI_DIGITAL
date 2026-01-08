<div class="topbar">
    <div class="brand">Absensi Digital</div>
    <div style="display:flex; align-items:center; gap:12px;">
        <span style="color:var(--muted); font-size:14px;">{{ auth()->user()->name ?? '' }}</span>
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline-secondary" style="padding:6px 10px;">Keluar</a>
    </div>
</div>
