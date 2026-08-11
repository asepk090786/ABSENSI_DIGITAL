@extends('layouts.app', ['pageSlug' => 'editor_modul'])

@section('title', 'Edit Modul Ajar')

@section('content')
<div class="app-shell" style="min-height: calc(100vh - 140px); background: #eaf0f6; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 16px rgba(0,0,0,.08);">
    <header class="app-header" style="padding: 14px 20px; display:flex; align-items:center; gap:14px; background: linear-gradient(115deg, #174780, #2d6ab6); color:#fff;">
        <div style="width: 42px; height: 42px; display:grid; place-items:center; border-radius: 10px; background:#fff; color:#235c9f; font-weight:700; font-size: 20px;">M</div>
        <div>
            <div style="font-weight:700; font-size:15px;">Edit Modul Ajar</div>
            <div style="font-size:12px; opacity:.9;">{{ $module->judul }}</div>
        </div>
        <div style="margin-left:auto; display:flex; align-items:center; gap:10px;">
            <button type="button" id="btn-sync-collabora" class="btn btn-sm btn-light text-primary">Simpan dan Kembali</button>
            <a href="{{ route('akademik.editor_modul.index') }}" class="btn btn-sm btn-light text-primary">Kembali ke Daftar</a>
        </div>
    </header>

    <main style="padding:20px; height: calc(100vh - 180px); overflow:hidden;">
        @if($wopiUrl)
        <div id="editor-a4-wrapper" style="background:#fff; height:100%; border-radius:8px; overflow:hidden; box-shadow:0 4px 18px rgba(0,0,0,.08);">
            <iframe src="{{ $wopiUrl }}" style="width:100%; height:100%; border:none;"></iframe>
        </div>
        @else
        <div class="alert alert-warning">
            <h4 class="alert-heading">Template tidak ditemukan</h4>
            <p>File template modul ajar belum tersedia. Silakan hubungi administrator untuk mengupload template DOCX di <code>storage/app/templates/template_modul_ajar.docx</code>.</p>
        </div>
        @endif
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnSync = document.getElementById('btn-sync-collabora');
    if (btnSync) {
        btnSync.addEventListener('click', function() {
            btnSync.disabled = true;
            btnSync.textContent = 'Menyimpan...';

            fetch('{{ url('modul-ajar') }}/{{ $module->id }}/sync-from-collabora', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    window.location.href = '{{ url('modul-ajar') }}/{{ $module->id }}/edit';
                } else {
                    throw new Error(json.message || json.error || 'Gagal menyinkronkan');
                }
            })
            .catch(err => {
                console.error(err);
                window.alert(err.message || 'Gagal menyimpan perubahan.');
                btnSync.disabled = false;
                btnSync.textContent = 'Simpan dan Kembali';
            });
        });
    }
});
</script>
@endsection
