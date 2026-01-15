<?php $__env->startSection('title','Tahun Ajaran'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Tahun Ajaran</h4>
                <a href="<?php echo e(route('setting.tahun_ajaran.create')); ?>" class="btn btn-primary btn-sm">Tambah</a>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Tahun</th><th>Status</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                        <?php $__currentLoopData = $tahuns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($t->nama_tahun); ?></td>
                                <td>
                                    <?php if($t->is_active): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Tidak Aktif</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo e(route('setting.tahun_ajaran.show', $t->id)); ?>" class="btn btn-info">Detail</a>
                                        <a href="<?php echo e(route('setting.tahun_ajaran.edit', $t->id)); ?>" class="btn btn-warning">Edit</a>
                                        <form action="<?php echo e(route('setting.tahun_ajaran.destroy', $t->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="btn btn-danger">Hapus</button>
                                        </form>
                                        <?php if(!$t->is_active): ?>
                                            <form action="<?php echo e(route('setting.tahun_ajaran.activate', $t->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-success">Aktifkan</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="<?php echo e(route('setting.tahun_ajaran.deactivate', $t->id)); ?>" method="POST" class="d-inline">
                                                <?php echo csrf_field(); ?>
                                                <button class="btn btn-secondary">Nonaktifkan</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['pageSlug' => 'setting'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/setting/tahun_ajaran.blade.php ENDPATH**/ ?>