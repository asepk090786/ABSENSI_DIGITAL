<?php $__env->startSection('title','Agenda Kelas'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Agenda Kelas</h4>
                <a href="<?php echo e(route('agenda_kelas.create')); ?>" class="btn btn-primary btn-sm">Tambah Agenda</a>
            </div>
            <div class="card-body">
                <?php if(session('success')): ?><div class="alert alert-success"><?php echo e(session('success')); ?></div><?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Tanggal</th><th>Kelas</th><th>Guru</th><th>Jam</th><th>Kegiatan</th></tr></thead>
                        <tbody>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($it->tanggal); ?></td>
                                <td><?php echo e(DB::table('kelas')->where('id',$it->kelas_id)->value('nama_kelas')); ?></td>
                                <td><?php echo e(DB::table('guru')->where('id',$it->guru_id)->value('nama')); ?></td>
                                <td><?php echo e(DB::table('jam_belajar')->where('id',$it->jam_belajar_id)->value('jam_mulai')); ?> - <?php echo e(DB::table('jam_belajar')->where('id',$it->jam_belajar_id)->value('jam_selesai')); ?></td>
                                <td><?php echo e($it->kegiatan); ?></td>
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

<?php echo $__env->make('layouts.app', ['pageSlug' => 'agenda'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/agenda_kelas/index.blade.php ENDPATH**/ ?>