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
                    <option value="{{ $kg->id }}" data-kategori="{{ $kg->kategori }}" {{ old('kegiatan_id', $item->kegiatan_id) == $kg->id ? 'selected' : '' }}>{{ $kg->nama_kegiatan }} ({{ $kg->kode_kegiatan ?? '' }})</option>
                @endforeach
            </select>
            <small class="text-muted">Jika tidak memilih, isi manual di bawah.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Kegiatan (manual)</label>
            <input name="nama_kegiatan" value="{{ old('nama_kegiatan', $item->nama_kegiatan) }}" class="form-control" />
        </div>
        <div class="mb-3">
            <label class="form-label">Jenis Kegiatan</label>
            <select name="jenis_kegiatan" class="form-control">
                <option value="">-- Pilih Jenis Kegiatan --</option>
                @foreach($jenisList as $jk)
                    <option value="{{ $jk->kode }}" {{ old('jenis_kegiatan', $item->jenis_kegiatan) == $jk->kode ? 'selected' : '' }}>{{ $jk->nama }} ({{ $jk->kode }})</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Tema Kegiatan</label>
            <input name="tema_kegiatan" value="{{ old('tema_kegiatan', $item->tema_kegiatan) }}" class="form-control" />
        </div>
        {{-- Jenis Kegiatan removed on edit: only edit nama kegiatan directly --}}
        <div class="mb-3">
            <label class="form-label">Pemateri</label>
            <div class="d-flex gap-2 align-items-center">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#pemateriModal">Pilih Pemateri</button>
                <span id="pemateriSummary" class="text-muted">Belum ada pemateri dipilih</span>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered" id="selectedPemateriTable">
                    <thead>
                        <tr><th>Nama Pemateri</th></tr>
                    </thead>
                    <tbody>
                        <tr><td class="text-muted">Belum ada pemateri dipilih</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        @php
            $selectedGuruIds = old('guru_ids', $item->peserta->where('peserta_type','guru')->pluck('peserta_id')->filter()->all());
            $selectedSiswaIds = old('siswa_ids', $item->peserta->where('peserta_type','siswa')->pluck('peserta_id')->filter()->all());
            $selectedExternal = old('external_participants', $item->peserta->where('peserta_type','external')->map(function($p){ return ['name' => $p->peserta_name, 'instansi' => $p->instansi]; })->all());
            $savedPemateri = collect($item->pemateri ?? []);
            $selectedPemateriIds = old('pemateri_guru_ids', $savedPemateri->map(function($name) use ($gurus) {
                $guru = $gurus->firstWhere('nama', $name);
                return $guru ? $guru->id : null;
            })->filter()->all());
            $pemateriNames = old('pemateri_names', $savedPemateri->reject(function($name) use ($gurus) {
                return $gurus->contains('nama', $name);
            })->implode(', '));
        @endphp
        <div class="mb-3">
            <label class="form-label">Pemateri lain (nama, pisahkan dengan koma)</label>
            <input name="pemateri_names" value="{{ $pemateriNames }}" class="form-control" placeholder="Nama Pemateri Tambahan, Contoh: Narasumber A, Narasumber B" />
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
            <label class="form-label">Peserta</label>
            <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#guruPesertaModal">Tambah Guru</button>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#siswaPesertaModal">Tambah Siswa</button>
                <span id="pesertaSummary" class="text-muted">Belum ada peserta dipilih</span>
            </div>
            <div class="table-responsive mt-3">
                <table class="table table-sm table-bordered" id="selectedPesertaTable">
                    <thead>
                        <tr><th>Tipe</th><th>Nama Peserta</th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="2" class="text-muted">Belum ada peserta dipilih</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Peserta Luar Guru/Siswa</label>
            <div class="row g-2 mb-2">
                <div class="col-md-5">
                    <input id="externalNameInput" type="text" class="form-control" placeholder="Nama Peserta Luar" />
                </div>
                <div class="col-md-5">
                    <input id="externalInstansiInput" type="text" class="form-control" placeholder="Instansi Peserta" />
                </div>
                <div class="col-md-2">
                    <button type="button" id="addExternalButton" class="btn btn-outline-primary w-100">Tambah</button>
                </div>
            </div>
            <div id="selectedExternalList" class="list-group">
                @foreach($selectedExternal as $index => $participant)
                    @if(!empty($participant['name']) || !empty($participant['instansi']))
                        <div class="list-group-item d-flex justify-content-between align-items-center selected-external-item" data-index="{{ $index }}">
                            <div>
                                <div><strong>{{ $participant['name'] ?? '-' }}</strong></div>
                                <div class="small text-muted">{{ $participant['instansi'] ?? '-' }}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-external">Hapus</button>
                            <input type="hidden" name="external_participants[{{ $index }}][name]" value="{{ $participant['name'] ?? '' }}" />
                            <input type="hidden" name="external_participants[{{ $index }}][instansi]" value="{{ $participant['instansi'] ?? '' }}" />
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
        <button class="btn btn-primary">Simpan Perubahan</button>

        <div class="modal fade" id="pemateriModal" tabindex="-1" aria-labelledby="pemateriModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pemateriModalLabel">Pilih Pemateri</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="selectAllPemateri">
                            <label class="form-check-label" for="selectAllPemateri">Pilih semua pemateri</label>
                        </div>
                        <div class="row g-3">
                            @foreach($gurus as $g)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pemateri_guru_ids[]" value="{{ $g->id }}" id="pemateriGuru{{ $g->id }}" {{ in_array($g->id, $selectedPemateriIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pemateriGuru{{ $g->id }}">{{ $g->nama }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="confirmPemateriSelection" data-bs-dismiss="modal">Tambah</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="guruPesertaModal" tabindex="-1" aria-labelledby="guruPesertaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="guruPesertaModalLabel">Pilih Guru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="selectAllGuruPeserta">
                            <label class="form-check-label" for="selectAllGuruPeserta">Pilih semua guru</label>
                        </div>
                        <div class="row g-2">
                            @foreach($gurus as $g)
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="guru_ids[]" value="{{ $g->id }}" id="pesertaGuru{{ $g->id }}" {{ in_array($g->id, $selectedGuruIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pesertaGuru{{ $g->id }}">{{ $g->nama }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="confirmGuruPesertaSelection" data-bs-dismiss="modal">Tambah</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="siswaPesertaModal" tabindex="-1" aria-labelledby="siswaPesertaModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="siswaPesertaModalLabel">Pilih Siswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="selectAllSiswaPeserta">
                            <label class="form-check-label" for="selectAllSiswaPeserta">Pilih semua siswa</label>
                        </div>
                        <div class="row g-2">
                            @foreach($siswas as $s)
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="siswa_ids[]" value="{{ $s->id }}" id="pesertaSiswa{{ $s->id }}" {{ in_array($s->id, $selectedSiswaIds) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="pesertaSiswa{{ $s->id }}">{{ $s->nama }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-primary" id="confirmSiswaPesertaSelection" data-bs-dismiss="modal">Tambah</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    const jenisSelect = document.querySelector('select[name="jenis_kegiatan"]');
    const kegiatanSelect = document.getElementById('kegiatanSelect');
    if(jenisSelect && kegiatanSelect) {
        function filterKegiatan(){
            const kode = jenisSelect.value || '';
            for(const opt of kegiatanSelect.options){
                const kat = opt.getAttribute('data-kategori') || '';
                opt.style.display = (kode === '' || kat === kode) ? '' : 'none';
            }
        }
        jenisSelect.addEventListener('change', filterKegiatan);
        filterKegiatan();
    }

    function updateSummary(containerSelector, summarySelector, emptyText) {
        const summary = document.getElementById(summarySelector);
        if (!summary) return;
        const checked = [...document.querySelectorAll(containerSelector + ' input[type="checkbox"]:checked')];
        if (checked.length === 0) {
            summary.textContent = emptyText;
            return;
        }
        const labels = checked.map(cb => {
            const label = document.querySelector(`label[for="${cb.id}"]`);
            return label ? label.textContent.trim() : cb.value;
        });
        summary.textContent = `${labels.length} dipilih: ${labels.slice(0, 5).join(', ')}${labels.length > 5 ? ', ...' : ''}`;
    }

    function addExternalParticipant() {
        const nameInput = document.getElementById('externalNameInput');
        const instansiInput = document.getElementById('externalInstansiInput');
        const list = document.getElementById('selectedExternalList');
        if (!nameInput || !instansiInput || !list) return;
        const name = nameInput.value.trim();
        const instansi = instansiInput.value.trim();
        if (!name && !instansi) return;
        const index = list.children.length;
        const row = document.createElement('div');
        row.className = 'list-group-item d-flex justify-content-between align-items-center selected-external-item';
        row.innerHTML = `
            <div>
                <div><strong>${name || '-'}</strong></div>
                <div class="small text-muted">${instansi || '-'}</div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger remove-external">Hapus</button>
        `;
        const inputName = document.createElement('input');
        inputName.type = 'hidden';
        inputName.name = `external_participants[${index}][name]`;
        inputName.value = name;
        const inputInstansi = document.createElement('input');
        inputInstansi.type = 'hidden';
        inputInstansi.name = `external_participants[${index}][instansi]`;
        inputInstansi.value = instansi;
        row.appendChild(inputName);
        row.appendChild(inputInstansi);
        list.appendChild(row);
        nameInput.value = '';
        instansiInput.value = '';
    }

    const pemateriContainer = '#pemateriModal';
    const pesertaContainers = ['#guruPesertaModal', '#siswaPesertaModal'];
    ['change', 'click'].forEach(eventName => {
        document.querySelectorAll(`${pemateriContainer} input[type="checkbox"]`).forEach(cb => cb.addEventListener(eventName, function(){
            updateSummary(pemateriContainer, 'pemateriSummary', 'Belum ada pemateri dipilih');
            renderSelectedPemateri();
        }));
        pesertaContainers.forEach(container => {
            document.querySelectorAll(`${container} input[type="checkbox"]`).forEach(cb => cb.addEventListener(eventName, function(){
                updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
                renderSelectedPeserta();
            }));
        });
    });

    document.getElementById('confirmPemateriSelection')?.addEventListener('click', function(){
        updateSummary(pemateriContainer, 'pemateriSummary', 'Belum ada pemateri dipilih');
        renderSelectedPemateri();
    });
    document.getElementById('confirmGuruPesertaSelection')?.addEventListener('click', function(){
        updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
        renderSelectedPeserta();
    });
    document.getElementById('confirmSiswaPesertaSelection')?.addEventListener('click', function(){
        updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
        renderSelectedPeserta();
    });

    document.getElementById('selectAllPemateri')?.addEventListener('change', function(){
        const checked = this.checked;
        document.querySelectorAll(`${pemateriContainer} input[name="pemateri_guru_ids[]"]`).forEach(cb => cb.checked = checked);
        updateSummary(pemateriContainer, 'pemateriSummary', 'Belum ada pemateri dipilih');
    });
    document.getElementById('selectAllGuruPeserta')?.addEventListener('change', function(){
        const checked = this.checked;
        document.querySelectorAll('#guruPesertaModal input[name="guru_ids[]"]').forEach(cb => cb.checked = checked);
        updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
    });
    document.getElementById('selectAllSiswaPeserta')?.addEventListener('change', function(){
        const checked = this.checked;
        document.querySelectorAll('#siswaPesertaModal input[name="siswa_ids[]"]').forEach(cb => cb.checked = checked);
        updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
    });

    const externalButton = document.getElementById('addExternalButton');
    if (externalButton) externalButton.addEventListener('click', addExternalParticipant);

    document.body.addEventListener('click', function(event){
        if (event.target.matches('.remove-external')) {
            event.target.closest('.selected-external-item')?.remove();
        }
    });

    updateSummary(pemateriContainer, 'pemateriSummary', 'Belum ada pemateri dipilih');
    updateSummary(pesertaContainers.join(', '), 'pesertaSummary', 'Belum ada peserta dipilih');
    renderSelectedPemateri();
    renderSelectedPeserta();
});
</script>
@endpush
