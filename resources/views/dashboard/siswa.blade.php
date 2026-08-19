@extends('layouts.app')

@section('title','Dashboard Siswa')

@section('content')
<div class="welcome-banner d-flex align-items-center gap-3">
    <div class="flex-shrink-0">
        <span class="avatar rounded-circle bg-white text-primary" style="width:2.8rem;height:2.8rem;font-size:1.2rem;display:flex;align-items:center;justify-content:center;">
            <i class="ti ti-school"></i>
        </span>
    </div>
    <div>
        <h3 class="mb-0 text-white">Dashboard Siswa</h3>
        <p class="mb-0 opacity-75" style="font-size:0.82rem;">Pantau absensi, agenda kelas, dan progress kehadiran Anda di sini.</p>
    </div>
</div>

<div class="alert alert-info d-flex align-items-center mb-3">
    <i class="ti ti-calendar-event me-3" style="font-size:1.5rem;"></i>
    <div>
        <strong>Tahun Ajaran:</strong> {{ $tahunAjaran ?? 'Belum ada tahun ajaran aktif' }} |
        <strong>Semester:</strong> {{ $semestrName ?? 'Belum ada semester aktif' }}
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon bg-primary-light text-primary mx-auto mb-2">
                <i class="ti ti-calendar-check"></i>
            </div>
            <div class="stat-value text-primary">{{ $attendanceSummary['present_percent'] ?? 0 }}%</div>
            <div class="stat-label mb-2">Kehadiran</div>
            <div class="progress" style="height:6px;">
                <div class="progress-bar bg-primary" style="width:{{ $attendanceSummary['present_percent'] ?? 0 }}%;"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon bg-accent-light text-accent mx-auto mb-2"><i class="ti ti-user-check"></i></div>
            <div class="stat-value text-accent">{{ $attendanceSummary['hadir'] ?? 0 }}</div>
            <div class="stat-label">Hadir</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#fef3c7;color:#d97706;"><i class="ti ti-alert-circle"></i></div>
            <div class="stat-value" style="color:#d97706;">{{ $attendanceSummary['terlambat'] ?? 0 }}</div>
            <div class="stat-label">Terlambat</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#dbeafe;color:#0ea5e9;"><i class="ti ti-medical-cross"></i></div>
            <div class="stat-value" style="color:#0ea5e9;">{{ $attendanceSummary['izin'] ?? 0 }}</div>
            <div class="stat-label">Izin</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#ede9fe;color:#7c3aed;"><i class="ti ti-heartbeat"></i></div>
            <div class="stat-value" style="color:#7c3aed;">{{ $attendanceSummary['sakit'] ?? 0 }}</div>
            <div class="stat-label">Sakit</div>
        </div>
    </div>
    <div class="col-sm-4 col-md-2">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#fee2e2;color:#ef4444;"><i class="ti ti-circle-x"></i></div>
            <div class="stat-value text-danger">{{ $attendanceSummary['alpa'] ?? 0 }}</div>
            <div class="stat-label">Alpa</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-chart-bar me-2 text-primary"></i>Progress Absensi</h5>
            </div>
            <div class="card-body">
                @if(($attendanceSummary['total'] ?? 0) > 0)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Hadir</span>
                                <span class="fw-bold text-accent">{{ $attendanceSummary['hadir'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-accent" style="width:{{ round(($attendanceSummary['hadir'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Terlambat</span>
                                <span class="fw-bold" style="color:#d97706;">{{ $attendanceSummary['terlambat'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-warning" style="width:{{ round(($attendanceSummary['terlambat'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Izin</span>
                                <span class="fw-bold" style="color:#0ea5e9;">{{ $attendanceSummary['izin'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-info" style="width:{{ round(($attendanceSummary['izin'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium" style="font-size:0.82rem;">Sakit</span>
                                <span class="fw-bold" style="color:#7c3aed;">{{ $attendanceSummary['sakit'] }}</span>
                            </div>
                            <div class="progress" style="height:8px;"><div class="progress-bar bg-purple" style="width:{{ round(($attendanceSummary['sakit'] / $attendanceSummary['total']) * 100, 1) }}%;"></div></div>
                        </div>
                    </div>
                @else
                    <div class="text-muted">Belum ada data absensi untuk tahun ajaran dan semester aktif.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-id me-2 text-primary"></i>Jabatan Kelas</h5>
            </div>
            <div class="card-body">
                @if($classPositionLabel)
                    <div class="badge bg-primary text-white mb-2 px-3 py-2">{{ $classPositionLabel }}</div>
                    <p class="mb-0" style="font-size:0.85rem;">Anda dapat mengakses fitur Agenda Kelas dan Absensi Kelas sesuai hak jabatan.</p>
                @else
                    <div class="text-muted" style="font-size:0.85rem;">Anda belum memiliki jabatan kelas aktif. Hubungi wali kelas atau admin untuk penempatan jabatan.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Access -->
<div class="row g-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title"><i class="ti ti-apps me-2 text-primary"></i>Akses Cepat</h5>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <a href="{{ route('absensi.rekap-bulanan') }}" class="quick-menu-card">
                            <div class="qm-icon bg-accent"><i class="ti ti-check"></i></div>
                            <div class="qm-label">Rekap Absensi</div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-6 col-md-3">
                            <a href="{{ route('absensi.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="quick-menu-card">
                                <div class="qm-icon bg-primary"><i class="ti ti-plus"></i></div>
                                <div class="qm-label">Buat Absensi</div>
                            </a>
                        </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <a href="{{ route('agenda_kelas.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#f59e0b;"><i class="ti ti-file-text"></i></div>
                            <div class="qm-label">Agenda Kelas</div>
                        </a>
                    </div>
                    <div class="col-6 col-md-3">
                        <a href="{{ auth()->user()->siswa && auth()->user()->siswa->kelas_id ? route('jadwal-kbm.show-by-kelas', ['kelas' => auth()->user()->siswa->kelas_id]) : route('agenda_kelas.index') }}" class="quick-menu-card">
                            <div class="qm-icon" style="background:#10b981;"><i class="ti ti-calendar-time"></i></div>
                            <div class="qm-label">Jadwal Kelas Saya</div>
                        </a>
                    </div>
                    @if($isSiswaOfficer)
                        <div class="col-6 col-md-3">
                            <a href="{{ route('agenda_kelas.create', ['kelas_id' => auth()->user()->siswa->kelas_id ?? '']) }}" class="quick-menu-card">
                                <div class="qm-icon" style="background:#ef4444;"><i class="ti ti-plus"></i></div>
                                <div class="qm-label">Buat Agenda</div>
                            </a>
                        </div>
                    @endif
                    @if((isset($activeVerification) && $activeVerification) || (isset($activeManualVerification) && $activeManualVerification))
                        <div class="col-6 col-md-3">
                            <button id="quickVerifyBtn" class="quick-menu-card btn btn-ghost {{ $studentAlreadyVerifiedToday ? 'disabled' : '' }}" type="button" {{ $studentAlreadyVerifiedToday ? 'disabled' : '' }}>
                                <div class="qm-icon" style="background:#06b6d4;"><i class="ti ti-lock-open"></i></div>
                                <div class="qm-label">{{ $studentAlreadyVerifiedToday ? 'Sudah Verifikasi' : 'Verifikasi Absen Hari ini' }}</div>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@if((isset($activeVerification) && $activeVerification) || (isset($activeManualVerification) && $activeManualVerification))
    @push('js')
    <!-- Modal for verification code input / manual verification -->
    <div class="modal fade" id="verifyCodeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ isset($verificationMode) && $verificationMode === 'manual' ? 'Verifikasi Absen' : 'Verifikasi Absen' }}</h5>
                        <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">&times;</button>
                    </div>
          <div class="modal-body">
            @if(isset($verificationMode) && $verificationMode === 'manual')
              <!-- Manual Verification Mode -->
              <div class="alert alert-info" role="alert">
                <i class="ti ti-info-circle me-2"></i>
                <strong>Verifikasi Manual</strong>
                <p class="mt-2 mb-0">Klik tombol "Verifikasi" di bawah untuk memverifikasi kehadiran Anda hari ini.</p>
              </div>
              <div id="verifyCodeAlert" class="alert d-none" role="alert"></div>
            @else
              <!-- Code Verification Mode -->
              <div class="mb-2">
                <label for="verifyCodeInput" class="form-label small">Masukkan kode verifikasi</label>
                <input id="verifyCodeInput" class="form-control" maxlength="20" autocomplete="off" />
              </div>
              <div id="verifyCodeAlert" class="alert d-none" role="alert"></div>
            @endif
          </div>
          <div class="modal-footer">
            <button type="button" id="verifyCodeSubmit" class="btn btn-primary">Verifikasi</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          </div>
        </div>
      </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function(){
            var btn = document.getElementById('quickVerifyBtn');
            var modalEl = document.getElementById('verifyCodeModal');
            var verifyInput = document.getElementById('verifyCodeInput');
            var verifySubmit = document.getElementById('verifyCodeSubmit');
            var verifyAlert = document.getElementById('verifyCodeAlert');
            var bootstrapModal = null;
            var usingJQueryModal = false;
            var studentAlreadyVerifiedToday = {{ !empty($studentAlreadyVerifiedToday) ? 'true' : 'false' }};
            var verificationExpiresAtTimestamp = parseInt('{{ $verificationExpiresAtTimestamp ?? '' }}', 10);
            var verificationExpiresAtRaw = '{{ $verificationExpiresAt ?? '' }}';
            var verificationMode = '{{ $verificationMode ?? '' }}';

            if (!isNaN(verificationExpiresAtTimestamp) && verificationExpiresAtTimestamp > 0 && verificationExpiresAtTimestamp < 1000000000000) {
                // Convert seconds to milliseconds if needed.
                verificationExpiresAtTimestamp *= 1000;
            }

            function normalizeVerifyButton() {
                if (!btn) return;
                if (!studentAlreadyVerifiedToday) {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                    btn.removeAttribute('aria-disabled');
                    btn.style.pointerEvents = '';
                }
            }

            normalizeVerifyButton();

            if (modalEl) {
                if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    try { bootstrapModal = new bootstrap.Modal(modalEl); } catch (e) { bootstrapModal = null; }
                } else if (window.jQuery || window.$) {
                    try { if (typeof jQuery(modalEl).modal === 'function') usingJQueryModal = true; } catch (e) { usingJQueryModal = false; }
                }
            }

            // Start countdown timer that disables verification when expired
            function startStudentVerificationTimer() {
                if (!verificationExpiresAtTimestamp || isNaN(verificationExpiresAtTimestamp)) return;

                function checkExpiry() {
                    var now = Date.now();
                    var remaining = Math.max(0, Math.round((verificationExpiresAtTimestamp - now) / 1000));

                    if (remaining <= 0 && btn) {
                        btn.disabled = true;
                        btn.classList.add('disabled');
                        btn.setAttribute('aria-disabled', 'true');
                        btn.style.pointerEvents = 'none';
                        var lbl = btn.querySelector('.qm-label');
                        if (lbl) lbl.textContent = 'Waktu verifikasi sudah habis';
                        var icon = btn.querySelector('.qm-icon');
                        if (icon) icon.style.background = '#6c757d';
                        if (verifySubmit) {
                            verifySubmit.disabled = true;
                            verifySubmit.classList.add('disabled');
                        }
                        return true;
                    }
                    return false;
                }

                // Check immediately
                if (checkExpiry()) return;

                // Then check every second
                setInterval(checkExpiry, 1000);
            }

            if(!btn || !modalEl) return;

            // Start the countdown timer
            startStudentVerificationTimer();
            if ((!verificationExpiresAtTimestamp || isNaN(verificationExpiresAtTimestamp)) && verificationExpiresAtRaw) {
                var parsed = Date.parse(verificationExpiresAtRaw.replace(' ', 'T'));
                if (!isNaN(parsed)) {
                    verificationExpiresAtTimestamp = parsed;
                    startStudentVerificationTimer();
                }
            }

            btn.addEventListener('click', function(){
                if (verifyInput && verificationMode !== 'manual') {
                    verifyInput.value = '';
                }
                if (verifyAlert) verifyAlert.classList.add('d-none');
                if (bootstrapModal) {
                    bootstrapModal.show();
                } else if (usingJQueryModal) {
                    jQuery(modalEl).modal('show');
                } else {
                    // fallback: simple prompt for code mode
                    if (verificationMode === 'manual') {
                        submitVerificationCode(null);
                    } else {
                        var kodeFallback = prompt('Masukkan kode verifikasi');
                        if (!kodeFallback) return;
                        submitVerificationCode(kodeFallback);
                    }
                    return;
                }
                if (verifyInput && verificationMode !== 'manual') {
                    setTimeout(function(){ if (verifyInput) verifyInput.focus(); }, 250);
                }
            });

            function showAlert(message, type){
                if (!verifyAlert) return;
                verifyAlert.classList.remove('d-none','alert-success','alert-danger','alert-warning');
                verifyAlert.classList.add('alert-' + (type || 'danger'));
                verifyAlert.textContent = message;
            }

            function hideModal() {
                if (bootstrapModal) { try { bootstrapModal.hide(); } catch (e) {} }
                else if (usingJQueryModal) { try { jQuery(modalEl).modal('hide'); } catch (e) {} }
            }

            function submitVerificationCode(kode) {
                if (verificationMode !== 'manual' && !kode) { 
                    showAlert('Masukkan kode verifikasi terlebih dahulu.', 'warning');
                    if (verifyInput) verifyInput.focus();
                    return;
                }
                verifySubmit.disabled = true;
                var payload = { 
                    ...({{ auth()->user()->siswa->kelas_id ?? '' }} ? {kelas_id: '{{ auth()->user()->siswa->kelas_id }}'} : {}), 
                    tanggal: '{{ date('Y-m-d') }}'
                };
                if (kode) {
                    payload.kode = kode;
                }
                fetch('{{ route('absensi.verify.student') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                }).then(function(resp){ return resp.json(); }).then(function(json){
                    verifySubmit.disabled = false;
                    if (json && json.success) {
                        showAlert(json.message || 'Verifikasi berhasil.', 'success');
                        // disable quick verify button immediately so user cannot re-submit
                        try {
                            if (btn) {
                                btn.disabled = true;
                                btn.classList.add('disabled');
                                btn.setAttribute('aria-disabled', 'true');
                                btn.style.pointerEvents = 'none';
                                var lbl = btn.querySelector('.qm-label');
                                if (lbl) lbl.textContent = 'Sudah Verifikasi';
                            }
                            if (verifySubmit) {
                                verifySubmit.disabled = true;
                                verifySubmit.classList.add('disabled');
                            }
                        } catch (e) { /* ignore */ }
                        setTimeout(function(){ hideModal(); window.location.reload(); }, 700);
                    } else {
                        showAlert(json.message || 'Kode verifikasi tidak valid atau kadaluarsa.', 'danger');
                    }
                }).catch(function(err){
                    verifySubmit.disabled = false;
                    console.error(err);
                    showAlert('Terjadi kesalahan jaringan.', 'danger');
                });
            }

            verifySubmit.addEventListener('click', function(){
                var kode = (verifyInput && verifyInput.value) ? verifyInput.value.trim() : '';
                submitVerificationCode(kode);
            });

            // allow enter key to submit if input exists
            if (verifyInput) verifyInput.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); verifySubmit.click(); } });
        });
    </script>
    @endpush
@endif
@endsection