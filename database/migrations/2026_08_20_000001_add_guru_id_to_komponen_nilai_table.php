<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('komponen_nilai', 'guru_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->foreignId('guru_id')->nullable()->after('id')->constrained('guru')->nullOnDelete();
                $table->index('guru_id');
            });
        }

        $components = DB::table('komponen_nilai as kn')
            ->leftJoin('capaian_pembelajarans as cp', 'cp.id', '=', 'kn.capaian_pembelajaran_id')
            ->leftJoin('users as u', 'u.id', '=', 'cp.user_id')
            ->whereNull('kn.guru_id')
            ->whereNotNull('u.guru_id')
            ->select('kn.id', 'u.guru_id')
            ->get();

        foreach ($components as $component) {
            DB::table('komponen_nilai')
                ->where('id', $component->id)
                ->whereNull('guru_id')
                ->update(['guru_id' => $component->guru_id]);
        }

        $owners = DB::table('rencana_pembelajaran_komponen_nilai as pivot')
            ->join('rencana_pembelajarans as rp', 'rp.id', '=', 'pivot.rencana_pembelajaran_id')
            ->whereNotNull('rp.guru_id')
            ->select('pivot.komponen_nilai_id', 'rp.guru_id')
            ->distinct()
            ->get()
            ->groupBy('komponen_nilai_id');

        foreach ($owners as $componentId => $rows) {
            $guruIds = $rows->pluck('guru_id')->unique();
            if ($guruIds->count() === 1) {
                DB::table('komponen_nilai')
                    ->where('id', $componentId)
                    ->whereNull('guru_id')
                    ->update(['guru_id' => $guruIds->first()]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('komponen_nilai', 'guru_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->dropForeign(['guru_id']);
                $table->dropIndex(['guru_id']);
                $table->dropColumn('guru_id');
            });
        }
    }
};