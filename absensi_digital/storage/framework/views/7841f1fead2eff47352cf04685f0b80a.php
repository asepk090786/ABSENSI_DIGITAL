<?php $__env->startSection('title','Semester'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Semester <?php if($active_tahun): ?>(<?php echo e($active_tahun->nama_tahun); ?>)<?php endif; ?></h4>
                <a href="<?php echo e(route('setting.semester.create')); ?>" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>
                <?php if(session('error')): ?><div class="alert alert-danger"><?php echo e(session('error')); ?></div><?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Semester</th><th>Tahun Ajaran</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $semesters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($s->nama_semester); ?></td>
                                <td><?php echo e(optional($s->tahunAjaran)->nama_tahun ?? 'N/A'); ?></td>
                                <td>
                                    <?php if($s->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('setting.semester.show', $s->id)); ?>" class="btn btn-info">Detail</a>
                                        <a href="<?php echo e(route('setting.semester.edit', $s->id)); ?>" class="btn btn-warning">Edit</a>
                                        <form action="<?php echo e(route('setting.semester.destroy', $s->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus semester ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-danger">Hapus</button>
                                        </form>
                                        <?php if(!$s->is_active): ?>
                                            <form action="<?php echo e(route('setting.semester.activate', $s->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-success">Aktifkan</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo e(route('setting.semester.deactivate', $s->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-secondary">Nonaktifkan</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada semester</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'setting'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/setting/semester.blade.php ENDPATH**/ ?>