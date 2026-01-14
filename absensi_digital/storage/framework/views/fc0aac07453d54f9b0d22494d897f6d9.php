<?php $__env->startSection('title','Guru'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h4 class="card-title mb-0">Data Guru</h4>
                    </div>
                    <div class="col-auto">
                        <div class="btn-list">
                            <!-- Import Button -->
                            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalImport">
                                <i class="ti ti-upload me-1"></i>Import Excel
                            </button>
                            
                            <!-- Export Button -->
                            <a href="<?php echo e(route('guru.export')); ?>" class="btn btn-info btn-sm">
                                <i class="ti ti-download me-1"></i>Export Excel
                            </a>
                            
                            <!-- Tambah Button -->
                            <a href="<?php echo e(route('guru.create')); ?>" class="btn btn-primary btn-sm">
                                <i class="ti ti-plus me-1"></i>Tambah Guru
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
                                <th>Nama</th>
                                <th>NIP</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Jenis Kelamin</th>
                                <th>Username</th>
                                <th>Status Akun</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($index + 1); ?></td>
                                <td><?php echo e($it->nama); ?></td>
                                <td><?php echo e($it->nip ?? '-'); ?></td>
                                <td><?php echo e($it->email ?? '-'); ?></td>
                                <td><?php echo e($it->telepon ?? '-'); ?></td>
                                <td>
                                    <?php if($it->jenis_kelamin == 'L'): ?>
                                        <span class="badge bg-blue-lt">Laki-laki</span>
                                    <?php elseif($it->jenis_kelamin == 'P'): ?>
                                        <span class="badge bg-pink-lt">Perempuan</span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($it->user): ?>
                                        <code><?php echo e($it->user->username); ?></code>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($it->user && $it->user->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php elseif($it->user): ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Belum Ada Akun</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-list">
                                        <a href="<?php echo e(route('guru.edit', $it->id)); ?>" class="btn btn-sm btn-outline-primary">
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
                                <td colspan="9" class="text-center text-muted">
                                    <i class="ti ti-info-circle me-2"></i>Belum ada data guru.
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

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Guru dari Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo e(route('guru.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file yang sudah diisi</li>
                        </ol>
                    </div>
                    
                    <div class="mb-3">
                        <a href="<?php echo e(route('guru.template')); ?>" class="btn btn-outline-primary btn-sm">
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

<!-- Form Delete (Hidden) -->
<form id="deleteForm" method="POST" style="display:none;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('js'); ?>
<script>
function confirmDelete(id) {
    if (confirm('Apakah Anda yakin ingin menghapus data guru ini? Akun terkait juga akan dihapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = `/guru/${id}`;
        form.submit();
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', ['pageSlug' => 'guru'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HYPE FLEX\Desktop\Project_absen\ABSENSI_DIGITAL\absensi_digital\resources\views/guru/index.blade.php ENDPATH**/ ?>