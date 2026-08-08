@extends('layouts.app', ['pageSlug' => 'setting'])

@section('title','Pengaturan Tampilan Dashboard')

@section('content')
    <h3>Pengaturan Tampilan Dashboard</h3>

    <div class="mb-3">
        <a href="{{ route('setting.backup') }}" class="btn btn-outline-secondary">
            <i class="ti ti-database me-1"></i> Backup Database
        </a>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <strong>Pengaturan Tampilan Dashboard</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('setting.jadwal_visibility.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="tampilkan_jadwal_guru" value="0">
                        <input type="hidden" name="tampilkan_jadwal_siswa" value="0">
                        <input type="hidden" name="tampilkan_nama_wali_kelas_guru" value="0">
                        <input type="hidden" name="tampilkan_nama_wali_kelas_siswa" value="0">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-3 pb-2">
                                        <strong>Tampilan Jadwal</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="tampilkan_jadwal_guru" name="tampilkan_jadwal_guru" value="1" {{ optional($sekolah)->tampilkan_jadwal_guru ?? optional($sekolah)->tampilkan_jadwal ?? true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tampilkan_jadwal_guru">Tampilkan jadwal pada akun guru</label>
                                        </div>

                                        @error('tampilkan_jadwal_guru')
                                            <div class="text-danger small mb-3">{{ $message }}</div>
                                        @enderror

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="tampilkan_jadwal_siswa" name="tampilkan_jadwal_siswa" value="1" {{ optional($sekolah)->tampilkan_jadwal_siswa ?? optional($sekolah)->tampilkan_jadwal ?? true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tampilkan_jadwal_siswa">Tampilkan jadwal pada akun siswa</label>
                                        </div>

                                        @error('tampilkan_jadwal_siswa')
                                            <div class="text-danger small mb-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-header border-0 pt-3 pb-2">
                                        <strong>Nama Wali Kelas</strong>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="tampilkan_nama_wali_kelas_guru" name="tampilkan_nama_wali_kelas_guru" value="1" {{ optional($sekolah)->tampilkan_nama_wali_kelas_guru ?? optional($sekolah)->tampilkan_nama_wali_kelas ?? true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tampilkan_nama_wali_kelas_guru">Tampilkan nama wali kelas pada akun guru</label>
                                        </div>

                                        @error('tampilkan_nama_wali_kelas_guru')
                                            <div class="text-danger small mb-3">{{ $message }}</div>
                                        @enderror

                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" id="tampilkan_nama_wali_kelas_siswa" name="tampilkan_nama_wali_kelas_siswa" value="1" {{ optional($sekolah)->tampilkan_nama_wali_kelas_siswa ?? optional($sekolah)->tampilkan_nama_wali_kelas ?? true ? 'checked' : '' }}>
                                            <label class="form-check-label" for="tampilkan_nama_wali_kelas_siswa">Tampilkan nama wali kelas pada akun siswa</label>
                                        </div>

                                        @error('tampilkan_nama_wali_kelas_siswa')
                                            <div class="text-danger small mb-3">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="wali_kelas_hidden_message" class="form-label">Pesan notifikasi jika nama wali kelas disembunyikan</label>
                            <input type="hidden" id="wali_kelas_hidden_message" name="wali_kelas_hidden_message" value="{{ old('wali_kelas_hidden_message', optional($sekolah)->wali_kelas_hidden_message) }}">
                            <div id="wali_kelas_editor" style="min-height:120px;">{!! old('wali_kelas_hidden_message', optional($sekolah)->wali_kelas_hidden_message) !!}</div>
                            <div class="form-text">Tampilan pesan ini akan muncul pada kartu kelas ketika nama wali kelas disembunyikan.</div>
                        </div>

                        @error('wali_kelas_hidden_message')
                            <div class="text-danger small mb-3">{{ $message }}</div>
                        @enderror

                        <div class="mb-3">
                            <label for="jadwal_maintenance_message" class="form-label">Pesan notifikasi (ditampilkan saat jadwal dinonaktifkan)</label>
                            <input type="hidden" id="jadwal_maintenance_message" name="jadwal_maintenance_message" value="{{ old('jadwal_maintenance_message', optional($sekolah)->jadwal_maintenance_message) }}">
                            <div id="jadwal_editor" style="min-height:120px;">{!! old('jadwal_maintenance_message', optional($sekolah)->jadwal_maintenance_message) !!}</div>
                            <div class="form-text">Anda dapat memasukkan teks biasa, menempel dari Word, atau menggunakan editor untuk menambahkan HTML sederhana.</div>
                        </div>

                        <p class="text-muted small">
                            Jika dinonaktifkan, preview jadwal di akun guru akan diganti dengan informasi bahwa jadwal masih dalam proses perbaikan.
                        </p>
                        <p class="text-muted small">
                            Jika nama wali kelas disembunyikan, akan ditampilkan notifikasi khusus di tampilan kelas untuk guru dan siswa.
                        </p>

                        <button type="submit" class="btn btn-primary btn-sm">Simpan pengaturan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
@endpush

@push('js')
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'clean']
            ];

            var quill = new Quill('#jadwal_editor', {
                modules: { toolbar: toolbarOptions, clipboard: { matchVisual: false } },
                theme: 'snow'
            });

            // set initial content from hidden input (already set via server-rendered HTML)
            var hidden = document.getElementById('jadwal_maintenance_message');
            var quill2 = new Quill('#wali_kelas_editor', {
                modules: { toolbar: toolbarOptions, clipboard: { matchVisual: false } },
                theme: 'snow'
            });

            // set initial content from hidden inputs (already set via server-rendered HTML)
            var hidden = document.getElementById('jadwal_maintenance_message');
            var hidden2 = document.getElementById('wali_kelas_hidden_message');
            if (hidden && hidden.value) {
                try { quill.clipboard.dangerouslyPasteHTML(hidden.value); } catch(e) {}
            }
            if (hidden2 && hidden2.value) {
                try { quill2.clipboard.dangerouslyPasteHTML(hidden2.value); } catch(e) {}
            }

            // on form submit, copy html to hidden inputs
            var form = document.querySelector('form[action="{{ route('setting.jadwal_visibility.update') }}"]');
            if (form) {
                form.addEventListener('submit', function() {
                    hidden.value = quill.root.innerHTML;
                    hidden2.value = quill2.root.innerHTML;
                });
            }
        });
    </script>
@endpush
