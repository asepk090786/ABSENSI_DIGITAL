@extends('layouts.app')

@section('title','Buat Pengembangan Diri')

@section('content')
    <h3>Buat Kegiatan Pengembangan Diri</h3>
    <form method="POST" action="{{ route('pengembangan.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama Kegiatan</label>
            <input name="nama_kegiatan" class="form-control" required />
        </div>
        <div class="mb-3">
            <label class="form-label">Jenis Kegiatan</label>
            <select name="jenis_kegiatan" class="form-control">
                <option value="">-- Pilih Jenis Kegiatan --</option>
                @foreach($jenisList as $jk)
                    <option value="{{ $jk->kode }}">{{ $jk->nama }} ({{ $jk->kode }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Pilih Pemateri (Guru) — bisa pilih lebih dari satu</label>
            <select name="pemateri_guru_ids[]" class="form-control" multiple>
                @foreach($gurus as $g)
                    <option value="{{ $g->id }}">{{ $g->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Pemateri lain (nama, pisahkan dengan koma)</label>
            <input name="pemateri_names" class="form-control" placeholder="Nama Pemateri Tambahan, Contoh: Narasumber A, Narasumber B" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="tanggal_mulai" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="tanggal_selesai" class="form-control" />
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
        <button class="btn btn-primary">Simpan</button>
    </form>
@endsection
