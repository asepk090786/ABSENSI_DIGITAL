

<?php $__env->startSection('title','Atur Jadwal KBM'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Atur Jadwal KBM - <?php echo e($kelas->nama_kelas); ?></h4>
                        <p class="text-muted mb-0 mt-1">
                            <small>
                                <i class="ti ti-user me-1"></i>Wali Kelas: <?php echo e($kelas->waliKelas->nama ?? '-'); ?> | 
                                <i class="ti ti-layer ms-2 me-1"></i>Tingkat: <?php echo e($kelas->tingkat_kelas ?? '-'); ?>

                                <?php if($kelas->jurusan): ?>
                                    | <i class="ti ti-books ms-2 me-1"></i>Jurusan: <?php echo e($kelas->jurusan); ?>

                                <?php endif; ?>
                            </small>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="<?php echo e(route('jadwal-kbm.index')); ?>" class="btn btn-secondary btn-sm">
                            <i class="ti ti-arrow-left me-1"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i><?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="<?php echo e(route('jadwal-kbm.store')); ?>" method="POST" id="formJadwal">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="kelas_id" value="<?php echo e($kelas->id); ?>">

                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong> Pilih guru dan mata pelajaran untuk setiap jam KBM. Sistem akan memvalidasi apakah guru tersedia di waktu yang dipilih.
                    </div>

                    <!-- Tabs untuk setiap hari -->
                    <ul class="nav nav-tabs mb-3" id="hariTab" role="tablist">
                        <?php
                            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        ?>
                        <?php $__currentLoopData = $hariList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hari): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link <?php echo e($index === 0 ? 'active' : ''); ?>" 
                                    id="<?php echo e(strtolower($hari)); ?>-tab" 
                                    data-bs-toggle="tab" 
                                    data-bs-target="#<?php echo e(strtolower($hari)); ?>" 
                                    type="button" 
                                    role="tab">
                                <?php echo e($hari); ?>

                            </button>
                        </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>

                    <div class="tab-content" id="hariTabContent">
                        <?php $__currentLoopData = $hariList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $hari): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="tab-pane fade <?php echo e($index === 0 ? 'show active' : ''); ?>" 
                             id="<?php echo e(strtolower($hari)); ?>" 
                             role="tabpanel">
                            
                            <?php
                                $jamHari = $jamBelajarByHari->get($hari, collect());
                                $existingHari = $existingJadwal->get($hari, collect());
                            ?>

                            <?php if($jamHari->isEmpty()): ?>
                                <div class="alert alert-warning">
                                    Tidak ada jam KBM yang diatur untuk hari <?php echo e($hari); ?>

                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="10%">Jam Ke</th>
                                                <th width="15%">Waktu</th>
                                                <th width="10%">Jenis</th>
                                                <th width="30%">Mata Pelajaran</th>
                                                <th width="30%">Guru Pengajar</th>
                                                <th width="5%">
                                                    <button type="button" class="btn btn-sm btn-success" onclick="copyFromPrevious('<?php echo e(strtolower($hari)); ?>')">
                                                        <i class="ti ti-copy"></i>
                                                    </button>
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $__currentLoopData = $jamHari; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php
                                                $existing = $existingHari->firstWhere('jam_ke', $jam->urutan);
                                            ?>
                                            <tr class="jadwal-row" data-hari="<?php echo e($hari); ?>" data-jam="<?php echo e($jam->urutan); ?>">
                                                <td class="text-center"><?php echo e($jam->urutan); ?></td>
                                                <td><?php echo e($jam->jam_mulai); ?> - <?php echo e($jam->jam_selesai); ?></td>
                                                <td>
                                                    <span class="badge <?php echo e($jam->jenis === 'KBM' ? 'bg-primary' : 'bg-secondary'); ?>">
                                                        <?php echo e($jam->jenis); ?>

                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if($jam->jenis === 'KBM'): ?>
                                                        <input type="hidden" name="jadwal[<?php echo e($hari); ?>_<?php echo e($jam->urutan); ?>][hari]" value="<?php echo e($hari); ?>">
                                                        <input type="hidden" name="jadwal[<?php echo e($hari); ?>_<?php echo e($jam->urutan); ?>][jam_ke]" value="<?php echo e($jam->urutan); ?>">
                                                        <input type="hidden" name="jadwal[<?php echo e($hari); ?>_<?php echo e($jam->urutan); ?>][jam_belajar_id]" value="<?php echo e($jam->id); ?>">
                                                        
                                                        <select name="jadwal[<?php echo e($hari); ?>_<?php echo e($jam->urutan); ?>][mata_pelajaran_id]" 
                                                                class="form-select form-select-sm mapel-select" 
                                                                data-hari="<?php echo e($hari); ?>" 
                                                                data-jam="<?php echo e($jam->urutan); ?>">
                                                            <option value="">-- Pilih Mata Pelajaran --</option>
                                                            <?php $__currentLoopData = $mataPelajaranList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mapel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($mapel->id); ?>" 
                                                                        <?php echo e($existing && $existing->mata_pelajaran_id == $mapel->id ? 'selected' : ''); ?>>
                                                                    <?php echo e($mapel->nama_mapel); ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?php echo e($jam->jenis); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if($jam->jenis === 'KBM'): ?>
                                                        <select name="jadwal[<?php echo e($hari); ?>_<?php echo e($jam->urutan); ?>][guru_id]" 
                                                                class="form-select form-select-sm guru-select" 
                                                                data-hari="<?php echo e($hari); ?>" 
                                                                data-jam="<?php echo e($jam->urutan); ?>">
                                                            <option value="">-- Pilih Guru --</option>
                                                            <?php $__currentLoopData = $guruList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $guru): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                <option value="<?php echo e($guru->id); ?>" 
                                                                        <?php echo e($existing && $existing->guru_id == $guru->id ? 'selected' : ''); ?>>
                                                                    <?php echo e($guru->nama); ?>

                                                                </option>
                                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                        </select>
                                                        <small class="text-danger konflik-warning" style="display:none;">
                                                            Guru sudah mengajar di kelas lain!
                                                        </small>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if($jam->jenis === 'KBM'): ?>
                                                        <button type="button" class="btn btn-sm btn-danger" onclick="clearJadwal(this)">
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan Jadwal
                        </button>
                        <a href="<?php echo e(route('jadwal-kbm.index')); ?>" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Check konflik guru saat memilih guru
    $('.guru-select').on('change', function() {
        const $row = $(this).closest('.jadwal-row');
        const guruId = $(this).val();
        const hari = $row.data('hari');
        const jamKe = $row.data('jam');
        const kelasId = <?php echo e($kelas->id); ?>;
        const $warning = $row.find('.konflik-warning');
        
        if (guruId) {
            $.ajax({
                url: '<?php echo e(route("jadwal-kbm.check-konflik-guru")); ?>',
                method: 'POST',
                data: {
                    _token: '<?php echo e(csrf_token()); ?>',
                    guru_id: guruId,
                    hari: hari,
                    jam_ke: jamKe,
                    kelas_id: kelasId
                },
                success: function(response) {
                    if (response.konflik) {
                        $warning.text(`Guru sudah mengajar di ${response.data.kelas.nama_kelas}`).show();
                    } else {
                        $warning.hide();
                    }
                }
            });
        } else {
            $warning.hide();
        }
    });
});

function clearJadwal(btn) {
    const $row = $(btn).closest('tr');
    $row.find('select').val('').trigger('change');
}

function copyFromPrevious(currentHari) {
    const hariList = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'];
    const currentIndex = hariList.indexOf(currentHari);
    
    if (currentIndex === 0) {
        alert('Tidak ada hari sebelumnya untuk disalin');
        return;
    }
    
    const previousHari = hariList[currentIndex - 1];
    
    if (confirm(`Salin jadwal dari ${previousHari.toUpperCase()}?`)) {
        $(`#${previousHari} .jadwal-row`).each(function(index) {
            const jamKe = $(this).data('jam');
            const mapelVal = $(this).find('.mapel-select').val();
            const guruVal = $(this).find('.guru-select').val();
            
            const $targetRow = $(`#${currentHari} .jadwal-row[data-jam="${jamKe}"]`);
            $targetRow.find('.mapel-select').val(mapelVal);
            $targetRow.find('.guru-select').val(guruVal).trigger('change');
        });
    }
}

// Validasi sebelum submit
$('#formJadwal').on('submit', function(e) {
    let hasKonflik = false;
    $('.konflik-warning:visible').each(function() {
        hasKonflik = true;
    });
    
    if (hasKonflik) {
        e.preventDefault();
        alert('Masih ada konflik jadwal guru. Silakan periksa kembali.');
        return false;
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'jadwal-kbm'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sman1\OneDrive\Desktop\Project_absensi\absensi_digital\resources\views/jadwal_kbm/create_by_kelas.blade.php ENDPATH**/ ?>