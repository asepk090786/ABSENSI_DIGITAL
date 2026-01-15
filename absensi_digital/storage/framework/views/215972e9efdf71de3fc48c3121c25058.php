<?php $__env->startSection('title','Edit Semester'); ?>

<?php $__env->startSection('content'); ?>
    <h3>Edit Semester</h3>

    <form method="POST" action="<?php echo e(route('setting.semester.update', $semester->id)); ?>">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="mb-3">
            <label>Tahun Ajaran</label>
            <input type="text" class="form-control" value="<?php echo e(optional($active_tahun)->nama_tahun ?? 'N/A'); ?>" disabled>
        </div>
        <div class="mb-3">
            <label>Nama Semester</label>
            <select name="nama_semester" class="form-select" required>
                <option value="Semester 1 (Ganjil)" <?php echo e(old('nama_semester', $semester->nama_semester) == 'Semester 1 (Ganjil)' ? 'selected' : ''); ?>>Semester 1 (Ganjil)</option>
                <option value="Semester 2 (Genap)" <?php echo e(old('nama_semester', $semester->nama_semester) == 'Semester 2 (Genap)' ? 'selected' : ''); ?>>Semester 2 (Genap)</option>
            </select>
            <?php $__errorArgs = ['nama_semester'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-danger"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <button class="btn btn-primary">Simpan</button>
        <a href="<?php echo e(route('setting.semester')); ?>" class="btn btn-link">Batal</a>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Project\ABSENSI_DIGITAL\absensi_digital\resources\views/setting/semester_edit.blade.php ENDPATH**/ ?>