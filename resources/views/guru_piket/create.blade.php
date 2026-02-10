@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tambah Data Guru Piket</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('guru_piket.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pilih Guru (Opsional)</label>
                                    <select name="guru_id" class="form-select @error('guru_id') is-invalid @enderror">
                                        <option value="">-- Pilih Guru atau Isi Manual --</option>
                                        @forelse($guru as $g)
                                            <option value="{{ $g->id }}" {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                                {{ $g->nama }} @if($g->nip)({{ $g->nip }})@endif
                                            </option>
                                        @empty
                                            <option value="" disabled>Data guru tidak tersedia</option>
                                        @endforelse
                                    </select>
                                    @error('guru_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Jika memilih guru, data nama dan NIP akan diambil otomatis</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP</label>
                                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                                    @error('nip')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Pilih Status</option>
                                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Tidak Aktif" {{ old('status') == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Foto</label>
                                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea name="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat') }}</textarea>
                                    @error('alamat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Telepon</label>
                                    <input type="text" name="telepon" class="form-control @error('telepon') is-invalid @enderror" value="{{ old('telepon') }}">
                                    @error('telepon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Hari Piket</label>
                                    @php
                                        $selectedHari = old('hari_piket', []);
                                    @endphp
                                    <div id="hari-piket-container" class="row g-2">
                                        @foreach($allHari as $hari)
                                            <div class="col-6 col-md-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="hari_piket[]" value="{{ $hari }}" id="hari-{{ $hari }}" {{ in_array($hari, $selectedHari) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="hari-{{ $hari }}">{{ $hari }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('hari_piket')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    @error('hari_piket.*')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-1">Pilihan hari mengikuti hari tanpa jadwal mengajar.</small>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy"></i> Simpan
                            </button>
                            <a href="{{ route('guru_piket.index') }}" class="btn btn-secondary">
                                <i class="ti ti-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const guruSelect = document.querySelector('select[name="guru_id"]');
    const emailInput = document.querySelector('input[name="email"]');
    const hariContainer = document.getElementById('hari-piket-container');
    const allHari = {!! json_encode($allHari) !!};
    const initialSelectedHari = {!! json_encode(old('hari_piket', [])) !!};
    const guruData = {!! json_encode($guru->mapWithKeys(function($item) use ($availableHariByGuru) {
        return [$item->id => [
            'nama' => $item->nama,
            'nip' => $item->nip,
            'email' => $item->user->email ?? $item->email,
            'available_hari' => $availableHariByGuru[$item->id] ?? [],
        ]];
    })->all()) !!};

    const getSelectedHari = function() {
        return Array.from(hariContainer.querySelectorAll('input[name="hari_piket[]"]:checked'))
            .map(function(input) { return input.value; });
    };

    const renderHari = function(days, selectedDays) {
        const selected = selectedDays || [];
        hariContainer.innerHTML = '';
        days.forEach(function(hari) {
            const col = document.createElement('div');
            col.className = 'col-6 col-md-3';

            const wrapper = document.createElement('div');
            wrapper.className = 'form-check';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'checkbox';
            input.name = 'hari_piket[]';
            input.value = hari;
            input.id = 'hari-' + hari;
            if (selected.indexOf(hari) !== -1) {
                input.checked = true;
            }

            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.htmlFor = input.id;
            label.textContent = hari;

            wrapper.appendChild(input);
            wrapper.appendChild(label);
            col.appendChild(wrapper);
            hariContainer.appendChild(col);
        });
    };

    renderHari(allHari, initialSelectedHari);

    if (guruSelect) {
        guruSelect.addEventListener('change', function() {
            const guruId = this.value;
            const currentSelectedHari = getSelectedHari();
            if (guruId && guruData[guruId]) {
                const data = guruData[guruId];
                document.querySelector('input[name="nama"]').value = data.nama;
                document.querySelector('input[name="nip"]').value = data.nip || '';
                if (emailInput) {
                    emailInput.value = data.email || '';
                }
                if (data.available_hari && data.available_hari.length) {
                    renderHari(data.available_hari, currentSelectedHari);
                } else {
                    renderHari(allHari, currentSelectedHari);
                }
            } else {
                if (emailInput) {
                    emailInput.value = '';
                }
                renderHari(allHari, currentSelectedHari);
            }
        });

        if (guruSelect.value && guruData[guruSelect.value]) {
            const data = guruData[guruSelect.value];
            if (data.available_hari && data.available_hari.length) {
                renderHari(data.available_hari, initialSelectedHari);
            }
        }
    }
});
</script>
@endsection
