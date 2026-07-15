@extends('layouts.app')

@section('title', 'ASC Time Table')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">ASC Time Table</h2>
                <div class="text-muted mt-1">Import jadwal dari aSc Timetables XML</div>
            </div>
            <div class="col-auto ms-auto">
                <div class="btn-list">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#importModal">
                        <i class="ti ti-upload me-2"></i>Import XML
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-check icon alert-icon"></i>
                </div>
                <div>
                    <h4 class="alert-title">Berhasil!</h4>
                    <div class="text-secondary">{{ session('success') }}</div>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex">
                <div>
                    <i class="ti ti-alert-circle icon alert-icon"></i>
                </div>
                <div>
                    <h4 class="alert-title">Error!</h4>
                    <div class="text-secondary">{{ session('error') }}</div>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title mb-4">Tentang ASC Time Table Import</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h4 class="mb-3">Fitur Import</h4>
                            <p>Import file XML dari aSc Timetables untuk memuat data:</p>
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="ti ti-clock text-primary me-2"></i>
                                    <strong>Jam Belajar</strong> - Periode waktu pelajaran
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-book text-success me-2"></i>
                                    <strong>Mata Pelajaran</strong> - Daftar mata pelajaran
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-user text-info me-2"></i>
                                    <strong>Guru</strong> - Data guru pengajar
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-building text-warning me-2"></i>
                                    <strong>Kelas</strong> - Daftar kelas
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-calendar text-danger me-2"></i>
                                    <strong>Jadwal KBM</strong> - Jadwal mengajar lengkap
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-clipboard-check text-purple me-2"></i>
                                    <strong>Tugas Guru</strong> - Otomatis dibuat dari jadwal
                                </li>
                            </ul>
                        </div>
                        
                        <div class="col-md-6">
                            <h4 class="mb-3">Cara Menggunakan</h4>
                            <ol>
                                <li class="mb-2">Export jadwal dari aplikasi aSc Timetables dalam format XML</li>
                                <li class="mb-2">Klik tombol <strong>"Import XML"</strong> di atas</li>
                                <li class="mb-2">Pilih file XML yang telah di-export</li>
                                <li class="mb-2">Klik <strong>"Import"</strong> dan tunggu proses selesai</li>
                                <li class="mb-2">Data akan otomatis tersinkronisasi ke sistem</li>
                            </ol>
                            
                            <div class="alert alert-info mt-3" role="alert">
                                <div class="d-flex">
                                    <div>
                                        <i class="ti ti-info-circle icon alert-icon"></i>
                                    </div>
                                    <div>
                                        <h4 class="alert-title">Catatan Penting</h4>
                                        <div class="text-secondary">
                                            <ul class="mb-0">
                                                <li>Sistem akan mendeteksi data duplikat berdasarkan <strong>Kode Guru</strong> dan <strong>Nama Guru</strong></li>
                                                <li>Anda dapat memilih untuk: <strong>Lewati</strong>, <strong>Replace Data Lama</strong>, atau <strong>Tambah Sebagai Baru</strong></li>
                                                <li>Nama guru yang sama tidak boleh memiliki kode guru yang berbeda (akan ditandai sebagai duplikat)</li>
                                                <li>Data mata pelajaran, kelas, dan jam belajar yang sudah ada tidak akan ditimpa</li>
                                                <li><strong>Tugas Guru</strong> akan otomatis dibuat/diperbarui berdasarkan jadwal yang diimport</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="card bg-light">
                        <div class="card-body">
                            <h4 class="mb-3">
                                <i class="ti ti-help-circle text-primary me-2"></i>
                                Bantuan & Dokumentasi
                            </h4>
                            <p class="mb-2">Untuk informasi lebih lanjut tentang aSc Timetables, kunjungi:</p>
                            <a href="https://www.asctimetables.com" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="ti ti-external-link me-2"></i>
                                Website aSc Timetables
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal modal-blur fade" id="importModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('asc_timetable.parse') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-upload me-2"></i>
                        Import XML Time Table
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">File XML</label>
                        <input type="file" name="xml_file" class="form-control" accept=".xml" required>
                        <small class="form-hint">
                            Format: XML dari aSc Timetables. Max: 10MB
                        </small>
                    </div>

                    <div class="alert alert-info" role="alert">
                        <div class="d-flex">
                            <div>
                                <i class="ti ti-info-circle icon alert-icon"></i>
                            </div>
                            <div>
                                <h4 class="alert-title">Informasi</h4>
                                <div class="text-secondary">
                                    Setelah upload, Anda akan melihat preview data sebelum diimport ke database. 
                                    Data yang sudah ada tidak akan ditimpa.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                        <i class="ti ti-x me-2"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-eye me-2"></i>Preview Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert-dismissible').fadeOut('slow');
    }, 5000);
</script>
@endpush
