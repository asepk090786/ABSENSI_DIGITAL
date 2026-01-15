

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-md-4">
            <!-- Photo Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="title">Foto Profile</h5>
                </div>
                <div class="card-body text-center">
                    <div class="mb-3">
                        <?php if(auth()->user()->foto): ?>
                            <img src="<?php echo e(asset('storage/' . auth()->user()->foto)); ?>" 
                                 alt="Profile Photo" 
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 id="preview-image">
                        <?php else: ?>
                            <img src="<?php echo e(asset('white')); ?>/img/default-avatar.png" 
                                 alt="Default Photo" 
                                 class="rounded-circle"
                                 style="width: 150px; height: 150px; object-fit: cover;"
                                 id="preview-image"
                                 onerror="this.src='<?php echo e(asset('white')); ?>/img/emilyz.jpg'">
                        <?php endif; ?>
                    </div>
                    <h5 class="title mb-1"><?php echo e(auth()->user()->name); ?></h5>
                    <p class="description mb-0"><?php echo e(auth()->user()->role->role_name ?? 'User'); ?></p>
                    <p class="description text-muted"><?php echo e(auth()->user()->username); ?></p>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card">
                <div class="card-body">
                    <h6 class="mb-3"><i class="ti ti-info-circle me-2"></i>Informasi Akun</h6>
                    <div class="mb-2">
                        <small class="text-muted">NIP:</small>
                        <div><?php echo e(auth()->user()->nip ?? '-'); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email:</small>
                        <div><?php echo e(auth()->user()->email ?? '-'); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Jenis Kelamin:</small>
                        <div><?php echo e(auth()->user()->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan'); ?></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Role:</small>
                        <div><?php echo e(auth()->user()->role->role_name ?? '-'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <!-- Edit Profile Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="title"><?php echo e(_('Edit Profile')); ?></h5>
                </div>
                <form method="post" action="<?php echo e(route('profile.update')); ?>" enctype="multipart/form-data" autocomplete="off">
                    <div class="card-body">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>

                        <?php echo $__env->make('alerts.success', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <div class="form-group<?php echo e($errors->has('foto') ? ' has-danger' : ''); ?>">
                            <label>Foto Profile</label>
                            <input type="file" name="foto" class="form-control<?php echo e($errors->has('foto') ? ' is-invalid' : ''); ?>" accept="image/*" onchange="previewImage(event)">
                            <small class="form-text text-muted">Format: JPG, PNG. Maksimal 2MB</small>
                            <?php echo $__env->make('alerts.feedback', ['field' => 'foto'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="form-group<?php echo e($errors->has('name') ? ' has-danger' : ''); ?>">
                            <label>Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control<?php echo e($errors->has('name') ? ' is-invalid' : ''); ?>" placeholder="Nama Lengkap" value="<?php echo e(old('name', auth()->user()->name)); ?>" required>
                            <?php echo $__env->make('alerts.feedback', ['field' => 'name'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="form-group<?php echo e($errors->has('nip') ? ' has-danger' : ''); ?>">
                            <label>NIP</label>
                            <input type="text" name="nip" class="form-control<?php echo e($errors->has('nip') ? ' is-invalid' : ''); ?>" placeholder="NIP" value="<?php echo e(old('nip', auth()->user()->nip)); ?>">
                            <?php echo $__env->make('alerts.feedback', ['field' => 'nip'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group<?php echo e($errors->has('email') ? ' has-danger' : ''); ?>">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control<?php echo e($errors->has('email') ? ' is-invalid' : ''); ?>" placeholder="Email" value="<?php echo e(old('email', auth()->user()->email)); ?>">
                                    <?php echo $__env->make('alerts.feedback', ['field' => 'email'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group<?php echo e($errors->has('jenis_kelamin') ? ' has-danger' : ''); ?>">
                                    <label>Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select name="jenis_kelamin" class="form-control<?php echo e($errors->has('jenis_kelamin') ? ' is-invalid' : ''); ?>" required>
                                        <option value="">Pilih</option>
                                        <option value="L" <?php echo e(old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'L' ? 'selected' : ''); ?>>Laki-laki</option>
                                        <option value="P" <?php echo e(old('jenis_kelamin', auth()->user()->jenis_kelamin) == 'P' ? 'selected' : ''); ?>>Perempuan</option>
                                    </select>
                                    <?php echo $__env->make('alerts.feedback', ['field' => 'jenis_kelamin'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" class="form-control" value="<?php echo e(auth()->user()->username); ?>" disabled>
                            <small class="form-text text-muted">Username tidak dapat diubah</small>
                        </div>

                        <div class="form-group">
                            <label>Role</label>
                            <input type="text" class="form-control" value="<?php echo e(auth()->user()->role->role_name ?? '-'); ?>" disabled>
                            <small class="form-text text-muted">Role tidak dapat diubah</small>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">
                            <i class="ti ti-device-floppy me-1"></i><?php echo e(_('Save')); ?>

                        </button>
                    </div>
                </form>
            </div>

            <!-- Change Password Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="title"><?php echo e(_('Password')); ?></h5>
                </div>
                <form method="post" action="<?php echo e(route('profile.password')); ?>" autocomplete="off">
                    <div class="card-body">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('put'); ?>

                        <?php echo $__env->make('alerts.success', ['key' => 'password_status'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <div class="form-group<?php echo e($errors->has('old_password') ? ' has-danger' : ''); ?>">
                            <label><?php echo e(__('Current Password')); ?> <span class="text-danger">*</span></label>
                            <input type="password" name="old_password" class="form-control<?php echo e($errors->has('old_password') ? ' is-invalid' : ''); ?>" placeholder="<?php echo e(__('Current Password')); ?>" value="" required>
                            <?php echo $__env->make('alerts.feedback', ['field' => 'old_password'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group<?php echo e($errors->has('password') ? ' has-danger' : ''); ?>">
                                    <label><?php echo e(__('New Password')); ?> <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control<?php echo e($errors->has('password') ? ' is-invalid' : ''); ?>" placeholder="<?php echo e(__('New Password')); ?>" value="" required>
                                    <?php echo $__env->make('alerts.feedback', ['field' => 'password'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                    <small class="form-text text-muted">Minimal 8 karakter</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo e(__('Confirm New Password')); ?> <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="<?php echo e(__('Confirm New Password')); ?>" value="" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-fill btn-primary">
                            <i class="ti ti-lock me-1"></i><?php echo e(_('Change password')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('preview-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', ['page' => __('User Profile'), 'pageSlug' => 'profile'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HYPE FLEX\Desktop\Project_absen\ABSENSI_DIGITAL\absensi_digital\resources\views/profile/edit.blade.php ENDPATH**/ ?>