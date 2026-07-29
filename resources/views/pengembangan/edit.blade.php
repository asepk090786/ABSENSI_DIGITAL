@extends('layouts.app')

@section('title','Edit Pengembangan')

@section('content')
    <h3>Edit Kegiatan</h3>
    <form method="POST" action="{{ route('pengembangan.update', $item->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Pilih Kegiatan (dari /kegiatan) atau ketik manual</label>
            <select name="kegiatan_id" id="kegiatanSelect" class="form-control">
                <option value="">-- Pilih dari daftar kegiatan --</option>
                @foreach($kegiatanList as $kg)
                    <option value="{{ $kg->id }}" data-kategori="{{ $kg->kategori }}" {{ (old('kegiatan_id') ?? null) == $kg->id ? 'selected' : '' }}>{{ $kg->nama_kegiatan }} ({{ $kg->kode_kegiatan ?? '' }})</option>
                @endforeach
            </select>
            <small class="text-muted">Jika tidak memilih, isi manual di bawah.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Kegiatan (manual)</label>
            <input name="nama_kegiatan" value="{{ old('nama_kegiatan', $item->nama_kegiatan) }}" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tema Kegiatan</label>
            <input name="tema_kegiatan" value="{{ old('tema_kegiatan', $item->tema_kegiatan) }}" class="form-control" />
        </div>
        {{-- Jenis Kegiatan removed on edit: only edit nama kegiatan directly --}}
        <div class="mb-3">
            <label class="form-label">Pilih Pemateri (Guru) — bisa pilih lebih dari satu</label>
            <select name="pemateri_guru_ids[]" class="form-control" multiple>
                @php $selectedPemateri = is_array($item->pemateri) ? $item->pemateri : []; @endphp
                @foreach($gurus as $g)
                    <option value="{{ $g->id }}" {{ in_array($g->nama, $selectedPemateri) ? 'selected' : '' }}>{{ $g->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Pemateri lain (nama, pisahkan dengan koma)</label>
            <input name="pemateri_names" value="{{ old('pemateri_names') }}" class="form-control" placeholder="Nama Pemateri Tambahan, Contoh: Narasumber A, Narasumber B" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control" value="{{ old('tanggal_mulai', optional($item->tanggal_mulai)->format('Y-m-d')) }}" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" class="form-control" value="{{ old('tanggal_selesai', optional($item->tanggal_selesai)->format('Y-m-d')) }}" />
        </div>
        <div class="mb-3">
            <label class="form-label">Pilih Guru (peserta)</label>
            <select name="guru_ids[]" class="form-control" multiple>
                @foreach($gurus as $g)
                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Pilih Siswa (peserta)</label>
            <select name="siswa_ids[]" class="form-control" multiple>
                @foreach($siswas as $s)
                    <option value="{{ $s->id }}">{{ $s->nama }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Simpan Perubahan</button>
    </form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const jenisSelect = document.querySelector('select[name="jenis_kegiatan"]');
    const kegiatanSelect = document.getElementById('kegiatanSelect');
    if(!jenisSelect || !kegiatanSelect) return;
    function filterKegiatan(){
        const kode = jenisSelect.value || '';
        for(const opt of kegiatanSelect.options){
            const kat = opt.getAttribute('data-kategori') || '';
            opt.style.display = (kode === '' || kat === kode) ? '' : 'none';
        }
    }
    jenisSelect.addEventListener('change', filterKegiatan);
    filterKegiatan();
});
</script>
</push>
