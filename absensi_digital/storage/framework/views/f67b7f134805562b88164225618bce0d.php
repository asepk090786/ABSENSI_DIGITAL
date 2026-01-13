

<?php $__env->startSection('title','Struktur Kurikulum'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Struktur Kurikulum</h4>
                <a href="<?php echo e(route('jadwal-kbm.index')); ?>" class="btn btn-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Kembali ke Jadwal KBM
                </a>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ti ti-check me-2"></i><?php echo e(session('success')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-circle me-2"></i><?php echo e(session('error')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <i class="ti ti-info-circle me-2"></i>
                    Pilih tingkat dan jurusan, lalu tentukan mata pelajaran yang berlaku. Pengaturan ini menjadi dasar jadwal per kelas dan guru.
                </div>

                <div class="row g-3 mb-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Tingkat</label>
                        <form method="GET" action="<?php echo e(route('kurikulum.index')); ?>" id="filterForm">
                            <div class="input-group">
                                <select name="tingkat" class="form-select" onchange="document.getElementById('filterForm').submit();">
                                    <?php $__currentLoopData = $tingkatList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tingkat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($tingkat); ?>" <?php echo e($tingkat == $selectedTingkat ? 'selected' : ''); ?>><?php echo e($tingkat); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                                <select name="jurusan" class="form-select" onchange="document.getElementById('filterForm').submit();">
                                    <?php $__currentLoopData = $jurusanList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jur): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($jur); ?>" <?php echo e($jur == $selectedJurusan ? 'selected' : ''); ?>><?php echo e($jur); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-6">
                        <form class="row g-2" method="POST" action="<?php echo e(route('kurikulum.add-item')); ?>">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tingkat" value="<?php echo e($selectedTingkat); ?>">
                            <input type="hidden" name="jurusan" value="<?php echo e($selectedJurusan); ?>">
                            <div class="col-md-6">
                                <label class="form-label">Mata Pelajaran</label>
                                <select name="mata_pelajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Mapel --</option>
                                    <?php $__currentLoopData = $mapel; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($m->id); ?>"><?php echo e($m->kode_mapel); ?> - <?php echo e($m->nama_mapel); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">JP</label>
                                <input type="number" name="jp" class="form-control" min="0" max="50" required>
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="ti ti-plus me-1"></i>Tambah
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="col-md-3 d-flex gap-2 justify-content-end">
                        <a class="btn btn-success btn-sm" href="<?php echo e(route('kurikulum.export', ['tingkat' => $selectedTingkat, 'jurusan' => $selectedJurusan])); ?>">
                            <i class="ti ti-download me-1"></i>Export
                        </a>
                        <form method="POST" action="<?php echo e(route('kurikulum.import')); ?>" enctype="multipart/form-data" class="d-flex gap-1">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="tingkat" value="<?php echo e($selectedTingkat); ?>">
                            <input type="hidden" name="jurusan" value="<?php echo e($selectedJurusan); ?>">
                            <input type="file" name="file" class="form-control form-control-sm" accept=".xlsx,.xls,.csv" required>
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="ti ti-upload me-1"></i>Import
                            </button>
                        </form>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3">Tabel Kurikulum per Tingkat</h5>
                <?php $__empty_1 = true; $__currentLoopData = $kurikulumByTingkat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tingkat => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong>Tingkat <?php echo e($tingkat); ?></strong>
                        </div>
                        <span class="badge bg-primary">Total JP: <?php echo e($totalJpPerTingkat[$tingkat] ?? 0); ?></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="6%">No</th>
                                        <th width="15%">Jurusan</th>
                                        <th width="20%">Kode</th>
                                        <th>Nama Mapel</th>
                                        <th width="10%" class="text-end">JP</th>
                                        <th width="12%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_2 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                    <tr>
                                        <td class="text-center"><?php echo e($idx + 1); ?></td>
                                        <td><?php echo e($row->jurusan ?? '-'); ?></td>
                                        <td><?php echo e($row->kode_mapel); ?></td>
                                        <td><?php echo e($row->nama_mapel); ?></td>
                                        <td class="text-end">
                                            <form class="d-flex justify-content-end gap-2" method="POST" action="<?php echo e(route('kurikulum.update-item', $row->id)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PUT'); ?>
                                                <input type="number" name="jp" class="form-control form-control-sm text-end" style="width:90px" min="0" max="50" value="<?php echo e($row->jp); ?>">
                                                <button class="btn btn-sm btn-outline-primary" type="submit" title="Update JP">
                                                    <i class="ti ti-device-floppy"></i>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="<?php echo e(route('kurikulum.delete-item', $row->id)); ?>" onsubmit="return confirm('Hapus mapel ini dari struktur?');" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Belum ada data.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total JP</th>
                                        <th class="text-end"><?php echo e($totalJpPerTingkat[$tingkat] ?? 0); ?></th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="alert alert-secondary">Belum ada struktur kurikulum tersimpan.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'kurikulum'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sman1\OneDrive\Desktop\Project_absensi\absensi_digital\resources\views/kurikulum/index.blade.php ENDPATH**/ ?>