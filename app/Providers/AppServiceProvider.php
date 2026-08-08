<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Sekolah;
use App\Services\SettingsManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        View::composer('*', function ($view) {
            $kelasBinaanBk = collect();
            $sekolah = null;
            $menuVisibility = [
                'guru' => [],
                'siswa' => [],
            ];

            try {
                $sekolah = Schema::hasTable('sekolah') ? Sekolah::first() : null;
                $settings = new SettingsManager();
                $menuVisibility = $settings->get('menu_visibility', $menuVisibility);

                if (Auth::check() && Auth::user()->hasRole('Guru BK')) {
                    $guru = Auth::user()->guru;

                    if ($guru && Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id')) {
                        $kelasBinaanBk = DB::table('kelas')
                            ->leftJoin('siswa', 'siswa.kelas_id', '=', 'kelas.id')
                            ->where('kelas.guru_bk_id', $guru->id)
                            ->select(
                                'kelas.id',
                                'kelas.nama_kelas',
                                'kelas.tingkat_kelas',
                                DB::raw('COUNT(siswa.id) as total_siswa')
                            )
                            ->groupBy('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_kelas')
                            ->orderBy('kelas.nama_kelas')
                            ->get();
                    }
                }
            } catch (\Throwable $e) {
                $kelasBinaanBk = collect();
                $sekolah = null;
            }

            $view->with('kelasBinaanBk', $kelasBinaanBk)
                 ->with('sekolah', $sekolah)
                 ->with('menuVisibility', $menuVisibility);
        });
    }
}
