<?php $__env->startSection('title','Pengaturan'); ?>

<?php $__env->startSection('content'); ?>
    <h3>Pengaturan Sistem</h3>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Tahun Ajaran Aktif</strong>
                </div>
                <div class="card-body">
                    <?php if($active_tahun): ?>
                        <p><strong><?php echo e($active_tahun->nama_tahun); ?></strong></p>
                    <?php else: ?>
                        <p class="text-danger">Tidak ada tahun ajaran aktif</p>
                    <?php endif; ?>
                    <a href="<?php echo e(route('setting.tahun_ajaran')); ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>Semester Aktif</strong>
                </div>
                <div class="card-body">
                    <?php if($active_semester): ?>
                        <p><strong><?php echo e($active_semester->nama_semester); ?></strong></p>
                    <?php else: ?>
                        <p class="text-danger">Tidak ada semester aktif</p>
                    <?php endif; ?>
                    <a href="<?php echo e(route('setting.semester')); ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'setting'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/setting/index.blade.php ENDPATH**/ ?>