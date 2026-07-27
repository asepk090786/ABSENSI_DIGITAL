@extends('layouts.app')

@section('title', 'Edit Absensi Kelas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0 pt-3 pb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title fw-semibold m-0">Edit Absensi Kelas</h3>
                        <a href="{{ route('absensi.show', $absensi->id) }}" class="btn btn-secondary">
                            <i class="ti ti-arrow-left me-1"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('absensi.update', $absensi->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="tanggal" class="form-label">Tanggal <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('tanggal') is-invalid @enderror" 
                                           id="tanggal" name="tanggal" value="{{ old('tanggal', $absensi->tanggal->format('Y-m-d')) }}" required>
                                    @error('tanggal')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="kelas_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('kelas_id') is-invalid @enderror" 
                                            id="kelas_id" name="kelas_id" required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($kelasList as $kelas)
                                            <option value="{{ $kelas->id }}" {{ old('kelas_id', $absensi->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                                {{ $kelas->nama_kelas }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="guru_id" class="form-label">Guru <span class="text-danger">*</span></label>
                                    <select class="form-select @error('guru_id') is-invalid @enderror" 
                                            id="guru_id" name="guru_id" required>
                                        <option value="">Pilih Guru</option>
                                        @foreach($guruList as $guru)
                                            <option value="{{ $guru->id }}" {{ old('guru_id', $absensi->guru_id) == $guru->id ? 'selected' : '' }}>
                                                {{ $guru->nama }} ({{ $guru->kode_guru }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="jam_belajar_id" class="form-label">Jam Belajar <span class="text-danger">*</span></label>
                                    <select class="form-select @error('jam_belajar_id') is-invalid @enderror" 
                                            id="jam_belajar_id" name="jam_belajar_id" required>
                                        <option value="">Pilih Jam Belajar</option>
                                        @foreach($jamBelajarList as $jam)
                                            <option value="{{ $jam->id }}" {{ old('jam_belajar_id', $absensi->jam_belajar_id) == $jam->id ? 'selected' : '' }}>
                                                Jam ke-{{ $jam->urutan }} ({{ $jam->jam_mulai }} - {{ $jam->jam_selesai }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('jam_belajar_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran <span class="text-danger">*</span></label>
                                    <select class="form-select @error('tahun_ajaran_id') is-invalid @enderror" 
                                            id="tahun_ajaran_id" name="tahun_ajaran_id" required>
                                        <option value="">Pilih Tahun Ajaran</option>
                                        @foreach($tahunAjaranList as $ta)
                                            <option value="{{ $ta->id }}" {{ old('tahun_ajaran_id', $absensi->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
                                                {{ $ta->nama_tahun }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tahun_ajaran_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label for="semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                                    <select class="form-select @error('semester_id') is-invalid @enderror" 
                                            id="semester_id" name="semester_id" required>
                                        <option value="">Pilih Semester</option>
                                        @foreach($semesterList as $sem)
                                            <option value="{{ $sem->id }}" {{ old('semester_id', $absensi->semester_id) == $sem->id ? 'selected' : '' }}>
                                                {{ $sem->nama_semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('semester_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mb-2">
                                    <label for="status_kelas" class="form-label">Status Kelas</label>
                                    <select class="form-control @error('status_kelas') is-invalid @enderror" id="status_kelas" name="status_kelas">
                                        <option value="">-- Pilih Status Kelas (opsional) --</option>
                                        <option value="Sangat Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Sangat Kondusif' ? 'selected' : '' }}>Sangat Kondusif</option>
                                        <option value="Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Kondusif' ? 'selected' : '' }}>Kondusif</option>
                                        <option value="Normal" {{ old('status_kelas', $absensi->status_kelas) === 'Normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="Kurang Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Kurang Kondusif' ? 'selected' : '' }}>Kurang Kondusif</option>
                                        <option value="Tidak Kondusif" {{ old('status_kelas', $absensi->status_kelas) === 'Tidak Kondusif' ? 'selected' : '' }}>Tidak Kondusif</option>
                                    </select>
                                    @error('status_kelas')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Opsional - Kondisi atau keterangan kelas</small>
                                </div>
                            </div>
                        </div>

                        <div id="siswaContainer" style="display: none;">
                            <div class="card mt-4">
                                <div class="card-header bg-success-subtle d-flex flex-wrap align-items-center gap-3">
                                    <h5 class="mb-0"><i class="ti ti-users me-2"></i>Daftar Siswa & Absensi</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive siswa-list">
                                        <table class="table table-bordered table-hover table-absensi">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th class="align-middle text-center" width="3%">No</th>
                                                    <th class="align-middle text-center" width="8%">NIS</th>
                                                    <th class="align-middle text-center" width="8%">NISN</th>
                                                    <th class="align-middle text-center" width="8%">FOTO</th>
                                                    <th class="align-middle text-center" width="20%">NAMA</th>
                                                    <th class="align-middle text-center" width="10%">JENIS KELAMIN</th>
                                                    <th class="text-center" width="5%">Hadir</th>
                                                    <th class="text-center" width="7%">Terlambat</th>
                                                    <th class="text-center" width="5%">Sakit</th>
                                                    <th class="text-center" width="5%">Izin</th>
                                                    <th class="text-center" width="8%">Alpa/Tanpa Keterangan</th>
                                                    <th class="align-middle text-center" width="15%">KETERANGAN</th>
                                                </tr>
                                            </thead>
                                            <tbody id="siswaTableBody">
                                                <tr>
                                                    <td colspan="12" class="text-center text-muted">
                                                        <i class="ti ti-info-circle me-1"></i>Memuat daftar siswa...
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="btnSubmit">
                                <i class="ti ti-check me-1"></i> Update
                            </button>
                            <a href="{{ route('absensi.show', $absensi->id) }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i> Batal
                            </a>
                        </div>

                        @push('js')
                        <script>
                            (function(){
                                var siswaContainer, siswaTableBody, btnSubmit, kelasId, tanggal, preferJamId;

                                function init() {
                                    siswaContainer = document.getElementById('siswaContainer');
                                    siswaTableBody = document.getElementById('siswaTableBody');
                                    btnSubmit = document.getElementById('btnSubmit');

                                    kelasId = '{{ $absensi->kelas_id }}';
                                    tanggal = '{{ $absensi->tanggal->format('Y-m-d') }}';
                                    preferJamId = '{{ $absensi->jam_belajar_id }}';

                                    function updateStatusBadge(value, row) {
                                        if (!row) return;
                                        row.classList.remove('bg-success-subtle','bg-warning-subtle','bg-info-subtle','bg-danger-subtle','status-terlambat');
                                        if (value === 'hadir') row.classList.add('bg-success-subtle');
                                        else if (value === 'terlambat') row.classList.add('status-terlambat');
                                        else if (value === 'sakit') row.classList.add('bg-warning-subtle');
                                        else if (value === 'izin') row.classList.add('bg-info-subtle');
                                        else if (value === 'alpa') row.classList.add('bg-danger-subtle');
                                    }

                                    fetch('{{ route('absensi.get-siswa') }}?kelas_id=' + kelasId + '&tanggal=' + tanggal + '&load_existing=1')
                                        .then(r => r.json())
                                        .then(function(data){
                                            if (!data.siswa || data.siswa.length === 0) {
                                                siswaTableBody.innerHTML = '<tr><td colspan="11" class="text-center text-warning"><i class="ti ti-alert-circle me-1"></i>Tidak ada siswa di kelas ini</td></tr>';
                                                btnSubmit.disabled = true;
                                                return;
                                            }

                                            var html = '';
                                            var defaultFemale = '{{ asset('images/default-avatar-female.svg') }}';
                                            var defaultMale = '{{ asset('images/default-avatar-male.svg') }}';
                                            data.siswa.forEach(function(siswa, idx){
                                                var fotoSrc = siswa.foto_url ? siswa.foto_url : (siswa.jenis_kelamin === 'P' ? defaultFemale : defaultMale);
                                                html += '<tr data-siswa-id="' + siswa.id + '">'
                                                    + '<td class="text-center">' + (idx+1) + '</td>'
                                                    + '<td class="text-center">' + (siswa.nis || '-') + '</td>'
                                                    + '<td class="text-center">' + (siswa.nisn || '-') + '</td>'
                                                    + '<td class="text-center"><img src="' + fotoSrc + '" alt="foto" style="width:40px;height:60px;object-fit:cover;border-radius:4px"></td>'
                                                    + '<td>' + siswa.nama + '</td>'
                                                    + '<td class="text-center">' + (siswa.jenis_kelamin || '-') + '</td>'
                                                    + '<td class="text-center"><input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="hadir"></td>'
                                                    + '<td class="text-center"><input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="terlambat"></td>'
                                                    + '<td class="text-center"><input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="sakit"></td>'
                                                    + '<td class="text-center"><input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="izin"></td>'
                                                    + '<td class="text-center"><input class="status-radio" type="radio" name="absensi_siswa[' + siswa.id + ']" value="alpa"></td>'
                                                    + '<td><input type="text" name="keterangan_siswa[' + siswa.id + ']" class="form-control form-control-sm" placeholder="Keterangan (opsional)"></td>'
                                                    + '</tr>';
                                            });
                                            siswaTableBody.innerHTML = html;
                                            siswaContainer.style.display = 'block';
                                            btnSubmit.disabled = false;

                                            var existing = data.existing_absensi || {};
                                            var map = {};
                                            if (existing[preferJamId] && existing[preferJamId].statuses) {
                                                map = existing[preferJamId].statuses;
                                            } else if (existing.daily && existing.daily.statuses) {
                                                map = existing.daily.statuses;
                                            } else {
                                                for (var k in existing) { if (existing[k] && existing[k].statuses) { map = existing[k].statuses; break; } }
                                            }

                                            document.querySelectorAll('#siswaTableBody tr').forEach(function(row){
                                                var sid = row.getAttribute('data-siswa-id');
                                                if (!sid) return;
                                                var status = map[sid];
                                                if (!status) return;
                                                var norm = String(status).toLowerCase();
                                                if (['hadir','terlambat','sakit','izin','alpa'].indexOf(norm) === -1) norm = 'alpa';
                                                var radio = row.querySelector('.status-radio[value="' + norm + '"]');
                                                if (radio) { radio.checked = true; updateStatusBadge(norm, row); }
                                            });

                                            document.querySelectorAll('.status-radio').forEach(function(r){
                                                r.addEventListener('change', function(){ updateStatusBadge(this.value, this.closest('tr')); });
                                            });
                                        })
                                        .catch(function(err){
                                            console.error('Gagal memuat siswa:', err);
                                            siswaTableBody.innerHTML = '<tr><td colspan="11" class="text-center text-danger"><i class="ti ti-alert-triangle me-1"></i>Terjadi kesalahan saat memuat data siswa</td></tr>';
                                            btnSubmit.disabled = true;
                                        });
                                }

                                // ensure script runs after tabler/bootstrap bundle loaded
                                if (window.tabler || window.bootstrap) {
                                    init();
                                } else {
                                    window.addEventListener('load', init);
                                }
                            })();
                        </script>
                        @endpush
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
