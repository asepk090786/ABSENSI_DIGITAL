

<?php $__env->startSection('title','Mata Pelajaran'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Mata Pelajaran</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                                <i class="ti ti-upload me-1"></i>Import Excel
                            </button>
                            <a href="<?php echo e(route('mata_pelajaran.export')); ?>" class="btn btn-info btn-sm">
                                <i class="ti ti-download me-1"></i>Export Excel
                            </a>
                            <a href="<?php echo e(route('mata_pelajaran.create')); ?>" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Mapel
                            </a>
                        </div>
                    </div>
                </div>
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

                <?php if(session('warning')): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="ti ti-alert-triangle me-2"></i><?php echo e(session('warning')); ?>

                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        <?php if(session('import_errors')): ?>
                            <hr>
                            <strong>Detail Error:</strong>
                            <ul class="mb-0">
                                <?php $__currentLoopData = session('import_errors'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li><?php echo e($error); ?></li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mapel</th>
                                <th>Kode</th>
                                <th>Kategori</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td>
                                    <a href="<?php echo e(route('mata_pelajaran.show', $it->id)); ?>" class="text-decoration-none"><?php echo e($it->nama_mapel); ?></a>
                                </td>
                                <td><?php echo e($it->kode_mapel ?? '-'); ?></td>
                                <td><?php echo e($it->kategori ?? '-'); ?></td>
                                <td>
                                    <div class="btn-list">
                                        <a href="<?php echo e(route('mata_pelajaran.edit', $it->id)); ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete(<?php echo e($it->id); ?>)">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada mata pelajaran.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Mata Pelajaran dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('mata_pelajaran.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Jika Kode Pelajaran sudah ada, nama akan diperbarui</li>
                            <li>Upload file yang sudah diisi</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <a href="<?php echo e(route('mata_pelajaran.template')); ?>" class="btn btn-outline-primary btn-sm">
                            <i class="ti ti-download me-1"></i>Download Template Excel
                        </a>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls" required>
                        <small class="form-hint">Format: .xlsx atau .xls, maksimal 2MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-1"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
function confirmDelete(id) {
    if (confirm('Hapus mata pelajaran ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = `/mata_pelajaran/${id}`;
        form.submit();
    }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'mata_pelajaran'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\sman1\OneDrive\Desktop\Project_absensi\absensi_digital\resources\views/mata_pelajaran/index.blade.php ENDPATH**/ ?>