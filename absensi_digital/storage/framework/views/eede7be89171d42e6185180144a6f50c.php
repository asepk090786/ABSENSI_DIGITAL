<?php $__env->startSection('title','Tambah Semester'); ?>

<?php $__env->startSection('content'); ?>
    <h3>Tambah Semester</h3>

    <form method="POST" action="<?php echo e(route('setting.semester.store')); ?>">
        <?php echo csrf_field(); ?>
        <div class="mb-3">
            <label>Tahun Ajaran</label>
            <input type="text" class="form-control" value="<?php echo e(optional($active_tahun)->nama_tahun ?? 'N/A'); ?>" disabled>
            <input type="hidden" name="tahun_ajaran_id" value="<?php echo e(optional($active_tahun)->id); ?>">
        </div>
        <div class="mb-3">
            <label>Nama Semester</label>
            <select name="nama_semester" class="form-select" required>
                <option value="Semester 1 (Ganjil)">Semester 1 (Ganjil)</option>
                <option value="Semester 2 (Genap)">Semester 2 (Genap)</option>
            </select>
        </div>
        <button class="btn btn-primary">Simpan</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/setting/semester_create.blade.php ENDPATH**/ ?>