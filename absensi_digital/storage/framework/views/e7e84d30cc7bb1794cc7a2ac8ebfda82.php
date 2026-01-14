<?php $__env->startSection('content'); ?>
<?php
    use Illuminate\Support\Facades\Storage;
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Data Sekolah</h3>
                    <?php if(!$sekolah): ?>
                        <a href="<?php echo e(route('sekolah.create')); ?>" class="btn btn-primary">
                            <i class="ti ti-plus"></i> Tambah Data Sekolah
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if($sekolah): ?>
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <?php if($sekolah->logo): ?>
                                    <?php
                                        $logoPath = $sekolah->logo;
                                        $logoUrl = Storage::disk('public')->exists($logoPath) ? Storage::url($logoPath) : null;
                                    ?>
                                    <?php if($logoUrl): ?>
                                        <img src="<?php echo e($logoUrl); ?>" alt="Logo Sekolah" class="img-fluid rounded mb-3" style="max-height: 200px;">
                                    <?php else: ?>
                                        <div class="bg-light rounded p-4 mb-3">
                                            <i class="ti ti-school" style="font-size: 100px; color: #ddd;"></i>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="bg-light rounded p-4 mb-3">
                                        <i class="ti ti-school" style="font-size: 100px; color: #ddd;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="200">Nama Sekolah</th>
                                            <td><?php echo e($sekolah->nama_sekolah); ?></td>
                                        </tr>
                                        <tr>
                                            <th>NPSN</th>
                                            <td><?php echo e($sekolah->npsn ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Jenjang</th>
                                            <td><span class="badge bg-primary"><?php echo e($sekolah->jenjang); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Status</th>
                                            <td><span class="badge bg-info"><?php echo e($sekolah->status); ?></span></td>
                                        </tr>
                                        <tr>
                                            <th>Akreditasi</th>
                                            <td><?php echo e($sekolah->akreditasi ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Alamat</th>
                                            <td><?php echo e($sekolah->alamat); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Kelurahan</th>
                                            <td><?php echo e($sekolah->kelurahan ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Kecamatan</th>
                                            <td><?php echo e($sekolah->kecamatan ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Kota</th>
                                            <td><?php echo e($sekolah->kota); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Provinsi</th>
                                            <td><?php echo e($sekolah->provinsi); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Kode Pos</th>
                                            <td><?php echo e($sekolah->kode_pos ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Telepon</th>
                                            <td><?php echo e($sekolah->telepon ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Email</th>
                                            <td><?php echo e($sekolah->email ?? '-'); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Website</th>
                                            <td>
                                                <?php if($sekolah->website): ?>
                                                    <a href="<?php echo e($sekolah->website); ?>" target="_blank"><?php echo e($sekolah->website); ?></a>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="mt-3">
                                    <a href="<?php echo e(route('sekolah.edit', $sekolah->id)); ?>" class="btn btn-warning">
                                        <i class="ti ti-edit"></i> Edit Data
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="ti ti-info-circle"></i> Belum ada data sekolah. Silakan tambahkan data sekolah terlebih dahulu.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HYPE FLEX\Desktop\Project_absen\ABSENSI_DIGITAL\absensi_digital\resources\views/sekolah/index.blade.php ENDPATH**/ ?>