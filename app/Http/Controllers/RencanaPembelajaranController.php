<?php

namespace App\Http\Controllers;

use App\Models\RencanaPembelajaran;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\CapaianPembelajaran;
use App\Models\JadwalKbm;
use Illuminate\Http\Request;

class RencanaPembelajaranController extends Controller
{
    /**
     * Display listing for a specific mata pelajaran & tingkat
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');
        $sort = $request->query('sort', 'terbaru');

        if (!$mataPelajaranId || !$tingkat) {
            $jadwalKbm = JadwalKbm::where('guru_id', $guru->id)
                ->with(['mataPelajaran', 'kelas'])
                ->whereNotNull('mata_pelajaran_id')
                ->get();

            $groupedByMapelAndTingkat = [];
            foreach ($jadwalKbm as $jadwal) {
                if (!$jadwal->kelas) {
                    continue;
                }
                $key = $jadwal->mata_pelajaran_id . '_' . $jadwal->kelas->tingkat_kelas;
                if (!isset($groupedByMapelAndTingkat[$key])) {
                    $groupedByMapelAndTingkat[$key] = [];
                }
                $groupedByMapelAndTingkat[$key][] = $jadwal;
            }

            $items = [];
            foreach ($groupedByMapelAndTingkat as $group) {
                $mataPelajaran = $group[0]->mataPelajaran;
                $kelas = collect($group)->pluck('kelas')->unique('id')->values();

                $item = clone $mataPelajaran;
                $item->kelas_list = $kelas;
                $item->kelas_names = $kelas->pluck('nama_kelas')->sort()->join(', ');
                $item->tingkat = $kelas->first()->tingkat_kelas ?? '-';

                $items[] = $item;
            }

            usort($items, function ($a, $b) {
                $cmp = strcasecmp($a->nama_mapel, $b->nama_mapel);
                if ($cmp !== 0) {
                    return $cmp;
                }
                return strcmp($a->tingkat, $b->tingkat);
            });

            return view('rencana_pembelajaran.index', [
                'items' => collect($items),
                'isLanding' => true,
            ]);
        }

        $query = RencanaPembelajaran::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with(['mataPelajaran', 'kelas']);

        // Apply sorting
        switch ($sort) {
            case 'judul_asc':
                $query->orderBy('judul', 'asc');
                break;
            case 'judul_desc':
                $query->orderBy('judul', 'desc');
                break;
            case 'status_asc':
                $query->orderBy('status', 'asc');
                break;
            case 'terlama':
                $query->orderBy('created_at', 'asc');
                break;
            case 'terbaru':
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $rawItems = $query->get();

        // Group berdasarkan judul agar judul yang sama per tingkat tampil satu kali
        $items = $rawItems
            ->groupBy(function ($item) {
                return mb_strtolower(trim((string) $item->judul));
            })
            ->map(function ($group) {
                $representative = $group->sortByDesc('created_at')->first();
                $representative->kelas_nama = $group->pluck('kelas.nama_kelas')
                    ->filter()
                    ->unique()
                    ->sort()
                    ->values();
                $representative->related_ids = $group->pluck('id')->values()->all();
                $representative->jumlah_kelas = count($representative->related_ids);

                return $representative;
            })
            ->values();

        // Re-apply sorting pada hasil grup
        switch ($sort) {
            case 'judul_asc':
                $items = $items->sortBy(function ($item) {
                    return mb_strtolower((string) $item->judul);
                })->values();
                break;
            case 'judul_desc':
                $items = $items->sortByDesc(function ($item) {
                    return mb_strtolower((string) $item->judul);
                })->values();
                break;
            case 'status_asc':
                $items = $items->sortBy(function ($item) {
                    return (string) $item->status . '|' . mb_strtolower((string) $item->judul);
                })->values();
                break;
            case 'terlama':
                $items = $items->sortBy('created_at')->values();
                break;
            case 'terbaru':
            default:
                $items = $items->sortByDesc('created_at')->values();
                break;
        }

        $mataPelajaran = MataPelajaran::find($mataPelajaranId);
        $previewItem = null;
        if ($request->has('preview')) {
            $previewId = $request->query('preview');
            $previewItem = RencanaPembelajaran::where('guru_id', $guru->id)
                ->where('mata_pelajaran_id', $mataPelajaranId)
                ->where('id', $previewId)
                ->with(['mataPelajaran', 'kelas'])
                ->first();
        }

        return view('rencana_pembelajaran.index', [
            'items' => $items,
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
            'previewItem' => $previewItem,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    private function scopedKomponenNilaiQuery()
    {
        $query = KomponenNilai::query();

        if (auth()->check() && auth()->user()->hasRole('Guru Mapel')) {
            $query->whereHas('capaianPembelajaran', function ($subQuery) {
                $subQuery->where('user_id', auth()->id());
            });
        }

        return $query;
    }

    private function getAllowedKelasIdsForRencana(int $mataPelajaranId, string $tingkat)
    {
        $guru = auth()->user()->guru;
        if (!$guru) {
            return collect();
        }

        return \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function ($k) use ($tingkat) {
                return $k->tingkat_kelas == $tingkat;
            })
            ->pluck('id')
            ->unique()
            ->values();
    }

    public function create(Request $request)
    {
        $guru = auth()->user()->guru;
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');

        $mataPelajaran = MataPelajaran::find($mataPelajaranId);
        
        // Get only classes that this guru teaches for this subject and level
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($tingkat) {
                return $k->tingkat_kelas == $tingkat;
            })
            ->unique('id')
            ->sortBy('nama_kelas');

        // Load assessment components created by this guru
        $komponenNilai = $this->scopedKomponenNilaiQuery()->orderBy('nama_komponen')->get();

        return view('rencana_pembelajaran.create', [
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
            'kelas' => $kelas,
            'komponenNilai' => $komponenNilai,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'judul' => 'required|string|max:255',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'metode' => 'nullable|string',
            'media' => 'nullable|string',
            'sumber' => 'nullable|string',
            'alokasi_waktu' => 'nullable|string',
            'praktik_pedagogis' => 'nullable|string',
            'lingkungan_pembelajaran' => 'nullable|string',
            'pemanfaatan_digital' => 'nullable|string',
            'pengalaman_pembelajaran' => 'nullable|string',
            'refleksi_pembelajaran' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'komponen_nilai_ids' => 'nullable|array',
            'komponen_nilai_ids.*' => 'exists:komponen_nilai,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $guru = auth()->user()->guru;
        $mataPelajaranId = $validated['mata_pelajaran_id'];
        $tingkat = Kelas::find($validated['kelas_ids'][0])->tingkat_kelas;
        $komponenNilaiIds = $validated['komponen_nilai_ids'] ?? [];

        if (!$this->isAdminOrKepala()) {
            $allowedKelasIds = $this->getAllowedKelasIdsForRencana($mataPelajaranId, $tingkat)->toArray();
            if (count(array_diff($validated['kelas_ids'], $allowedKelasIds)) > 0) {
                abort(403, 'Salah satu kelas tidak termasuk dalam jadwal pelajaran Anda.');
            }
        }

        if (!empty($komponenNilaiIds) && auth()->check() && auth()->user()->hasRole('Guru Mapel')) {
            $allowedKomponenIds = $this->scopedKomponenNilaiQuery()
                ->whereIn('id', $komponenNilaiIds)
                ->pluck('id')
                ->toArray();

            if (count($allowedKomponenIds) !== count($komponenNilaiIds)) {
                abort(403, 'Akses ditolak untuk salah satu komponen penilaian.');
            }
        }

        // Create rencana pembelajaran for each selected class
        foreach ($validated['kelas_ids'] as $kelasId) {
            $data = $validated;
            $data['guru_id'] = $guru->id;
            $data['kelas_id'] = $kelasId;
            unset($data['kelas_ids']);
            unset($data['komponen_nilai_ids']);
            
            $rencana = RencanaPembelajaran::create($data);
            
            // Sync komponen penilaian
            if (!empty($komponenNilaiIds)) {
                $rencana->komponenNilai()->sync($komponenNilaiIds);
            }
        }

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', 'Rencana pembelajaran berhasil dibuat untuk ' . count($validated['kelas_ids']) . ' kelas.');
    }

    /**
     * Display the specified resource.
     */
    private function isAdminOrKepala(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']);
    }

    private function authorizeRencanaPembelajaran(RencanaPembelajaran $rencanaPembelajaran)
    {
        if ($this->isAdminOrKepala()) {
            return;
        }

        $guru = auth()->user()->guru;
        if (!$guru || $rencanaPembelajaran->guru_id !== $guru->id) {
            abort(403, 'Anda tidak memiliki akses ke rencana pembelajaran ini.');
        }
    }

    public function show(RencanaPembelajaran $rencanaPembelajaran)
    {
        $this->authorizeRencanaPembelajaran($rencanaPembelajaran);

        return view('rencana_pembelajaran.show', ['item' => $rencanaPembelajaran]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelajaran $rencanaPembelajaran)
    {
        $this->authorizeRencanaPembelajaran($rencanaPembelajaran);

        $guru = auth()->user()->guru;
        $guruId = $guru ? $guru->id : $rencanaPembelajaran->guru_id;
        
        // Get only classes for this subject and level
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guruId)
            ->where('mata_pelajaran_id', $rencanaPembelajaran->mata_pelajaran_id)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($rencanaPembelajaran) {
                return $k->tingkat_kelas == $rencanaPembelajaran->kelas->tingkat_kelas;
            })
            ->unique('id')
            ->sortBy('nama_kelas');
        
        // Load assessment components created by this guru
        $komponenNilai = $this->scopedKomponenNilaiQuery()->orderBy('nama_komponen')->get();
        
        // Load selected komponen for this rencana
        $selectedKomponenIds = $rencanaPembelajaran->komponenNilai()->pluck('komponen_nilai.id')->toArray();
        
        // Load all Capaian Pembelajaran
        $capaianPembelajaran = CapaianPembelajaran::orderBy('nama_capaian_pembelajaran')->get();
        
        return view('rencana_pembelajaran.edit', [
            'item' => $rencanaPembelajaran,
            'kelas' => $kelas,
            'komponenNilai' => $komponenNilai,
            'selectedKomponenIds' => $selectedKomponenIds,
            'capaianPembelajaran' => $capaianPembelajaran,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RencanaPembelajaran $rencanaPembelajaran)
    {
        $this->authorizeRencanaPembelajaran($rencanaPembelajaran);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'judul' => 'required|string|max:255',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'metode' => 'nullable|string',
            'media' => 'nullable|string',
            'sumber' => 'nullable|string',
            'alokasi_waktu' => 'nullable|string',
            'praktik_pedagogis' => 'nullable|string',
            'lingkungan_pembelajaran' => 'nullable|string',
            'pemanfaatan_digital' => 'nullable|string',
            'pengalaman_pembelajaran' => 'nullable|string',
            'refleksi_pembelajaran' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'komponen_nilai_ids' => 'nullable|array',
            'komponen_nilai_ids.*' => 'exists:komponen_nilai,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $komponenNilaiIds = $validated['komponen_nilai_ids'] ?? [];
        unset($validated['komponen_nilai_ids']);

        if (!empty($komponenNilaiIds) && auth()->check() && auth()->user()->hasRole('Guru Mapel')) {
            $allowedKomponenIds = $this->scopedKomponenNilaiQuery()
                ->whereIn('id', $komponenNilaiIds)
                ->pluck('id')
                ->toArray();

            if (count($allowedKomponenIds) !== count($komponenNilaiIds)) {
                abort(403, 'Akses ditolak untuk salah satu komponen penilaian.');
            }
        }

        $rencanaPembelajaran->update($validated);
        
        // Sync komponen penilaian
        $rencanaPembelajaran->komponenNilai()->sync($komponenNilaiIds);

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $rencanaPembelajaran->mata_pelajaran_id,
                'tingkat' => $rencanaPembelajaran->kelas->tingkat_kelas,
            ])
            ->with('success', 'Rencana pembelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RencanaPembelajaran $rencanaPembelajaran)
    {
        $this->authorizeRencanaPembelajaran($rencanaPembelajaran);

        $mataPelajaranId = $rencanaPembelajaran->mata_pelajaran_id;
        $tingkat = $rencanaPembelajaran->kelas->tingkat_kelas;

        $rencanaPembelajaran->delete();

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', 'Rencana pembelajaran berhasil dihapus.');
    }

    /**
     * Download template Word untuk Rencana Pembelajaran
     */
    public function templateDownload()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'TEMPLATE RENCANA PEMBELAJARAN',
            ['bold' => true, 'size' => 14],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText('');
        
        // Information section
        $section->addText('INFORMASI UMUM', ['bold' => true, 'size' => 12]);
        
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        $table->addRow();
        $table->addCell(2500)->addText('Judul', ['bold' => true]);
        $table->addCell(5500)->addText('[Masukkan judul rencana pembelajaran di sini]');
        
        $table->addRow();
        $table->addCell(2500)->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(5500)->addText('[Nama mata pelajaran]');
        
        $table->addRow();
        $table->addCell(2500)->addText('Kelas / Fase', ['bold' => true]);
        $table->addCell(5500)->addText('[Kelas / Fase]');
        
        $table->addRow();
        $table->addCell(2500)->addText('Status', ['bold' => true]);
        $table->addCell(5500)->addText('[Draft / Published]');
        
        $table->addRow();
        $table->addCell(2500)->addText('Alokasi Waktu', ['bold' => true]);
        $table->addCell(5500)->addText('[... JP]');
        
        $section->addText('');
        
        $section->addText('CAPAIAN PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Tuliskan capaian pembelajaran sesuai dengan format yang berlaku]');
        $section->addText('');
        
        $section->addText('TUJUAN PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Sebutkan tujuan pembelajaran yang mengacu pada capaian pembelajaran]');
        $section->addText('');
        
        $section->addText('METODE PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Metode: ceramah, diskusi, tanya jawab, presentasi, penugasan, refleksi, dll.]');
        $section->addText('');
        
        $section->addText('MEDIA PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Buku, SIMADIS, GeoGebra, PowerPoint, Video, Internet, LKPD Digital, dll.]');
        $section->addText('');
        
        $section->addText('SUMBER BELAJAR', ['bold' => true, 'size' => 12]);
        $section->addText('[Referensi buku, website, lembar kegiatan, atau sumber lain]');
        $section->addText('');
        
        $section->addText('PRAKTIK PEDAGOGIS', ['bold' => true, 'size' => 12]);
        $section->addText('[Model Pembelajaran, Metode, Pendekatan, dll.]');
        $section->addText('');
        
        $section->addText('LINGKUNGAN PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Ruang fisik, ruang virtual, budaya belajar, dukungan lingkungan]');
        $section->addText('');
        
        $section->addText('PEMANFAATAN DIGITAL', ['bold' => true, 'size' => 12]);
        $section->addText('[Buku, SIMADIS, GeoGebra, PowerPoint, Video, Internet, LKPD Digital, dll.]');
        $section->addText('');
        
        $section->addText('PENGALAMAN PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Pendahuluan, Inti, Penutup. Tuliskan kegiatan pembelajaran pada setiap tahap.]');
        $section->addText('');
        
        $section->addText('REFLEKSI PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Refleksi guru dan peserta didik]');
        $section->addText('');
        
        $section->addText('PENILAIAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Diagnostik, Formatif, Observasi, Kuis, Sumatif, dll.]');
        
        // Generate filename using the attached template name
        $filename = 'Template_Rencana_Pembelajaran.docx';
        
        // Create writer and output to stream
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        
        // Use output buffering to capture the output
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($content));
    }

    /**
     * Export rencana pembelajaran to Word document
     */
    public function export(RencanaPembelajaran $rencanaPembelajaran)
    {
        $this->authorizeRencanaPembelajaran($rencanaPembelajaran);

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'RENCANA PEMBELAJARAN',
            ['bold' => true, 'size' => 14],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText(
            $rencanaPembelajaran->mataPelajaran->nama_mapel . ' - ' . $rencanaPembelajaran->kelas->nama_kelas,
            ['size' => 12],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText('');
        
        // Information table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        $rowStyle = ['bgColor' => 'D3D3D3'];
        $table->addRow();
        $table->addCell(2000)->addText('Judul', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->judul);
        
        $table->addRow();
        $table->addCell(2000)->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->mataPelajaran->nama_mapel);
        
        $table->addRow();
        $table->addCell(2000)->addText('Kelas', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->kelas->nama_kelas);
        
        $table->addRow();
        $table->addCell(2000)->addText('Status', ['bold' => true]);
        $table->addCell(6000)->addText(ucfirst($rencanaPembelajaran->status));
        
        if ($rencanaPembelajaran->tanggal_mulai || $rencanaPembelajaran->tanggal_selesai) {
            $table->addRow();
            $table->addCell(2000)->addText('Periode', ['bold' => true]);
            $periode = '';
            if ($rencanaPembelajaran->tanggal_mulai) {
                $periode = $rencanaPembelajaran->tanggal_mulai->format('d/m/Y');
            }
            if ($rencanaPembelajaran->tanggal_selesai) {
                $periode .= ' - ' . $rencanaPembelajaran->tanggal_selesai->format('d/m/Y');
            }
            $table->addCell(6000)->addText($periode);
        }
        
        $section->addText('');
        
        // Details sections
        if ($rencanaPembelajaran->capaian_pembelajaran) {
            $section->addText('Capaian Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->capaian_pembelajaran);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->tujuan) {
            $section->addText('Tujuan Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->tujuan);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->metode) {
            $section->addText('Metode Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->metode);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->media) {
            $section->addText('Media Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->media);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->sumber) {
            $section->addText('Sumber Belajar', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->sumber);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->penilaian) {
            $section->addText('Penilaian', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->penilaian);
        }
        
        // Generate filename
        $filename = 'RP_' . \Illuminate\Support\Str::slug($rencanaPembelajaran->judul) . '_' . time() . '.docx';
        
        // Create writer and output to stream
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        
        // Use output buffering to capture the output
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();
        
        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Length', strlen($content));
    }

    /**
     * Show import form
     */
    public function importForm(Request $request)
    {
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');

        $guru = auth()->user()->guru;
        $mataPelajaran = MataPelajaran::find($mataPelajaranId);
        
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($tingkat) {
                return $k->tingkat_kelas == $tingkat;
            })
            ->unique('id')
            ->sortBy('nama_kelas');

        return view('rencana_pembelajaran.import', [
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Process import from Word document
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:docx|max:5120', // 5MB max
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
        ]);

        try {
            $file = $request->file('file');
            $docxPath = \Storage::putFile('rencana_pembelajaran/docx', $file);
            $filePath = storage_path('app/' . $docxPath);

            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);

            $extractedText = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                        foreach ($element->getRows() as $row) {
                            $cells = [];
                            foreach ($row->getCells() as $cell) {
                                $cells[] = $this->extractWordElementText($cell);
                            }
                            $rowText = trim(implode(' | ', array_filter($cells)));
                            if ($rowText !== '') {
                                $extractedText .= $rowText . "\n";
                            }
                        }
                    } else {
                        $line = $this->extractWordElementText($element);
                        if ($line !== '') {
                            $extractedText .= $line . "\n";
                        }
                    }
                }
            }

            $data = $this->parseWordDocument($extractedText);

            if (empty($data['judul'])) {
                return back()->with('error', 'Tidak dapat menemukan judul dalam dokumen Word.');
            }

            $data['status'] = isset($data['status']) ? strtolower(trim($data['status'])) : 'draft';
            if (! in_array($data['status'], ['draft', 'published'])) {
                $data['status'] = 'draft';
            }

            $data['html_content'] = $this->convertDocxToHtml($filePath);
            $mataPelajaranId = $request->input('mata_pelajaran_id');
            $kelasIds = $request->input('kelas_ids');
            $selectedKelas = Kelas::whereIn('id', $kelasIds)->get();
            $mataPelajaran = MataPelajaran::find($mataPelajaranId);

            if (!$this->isAdminOrKepala()) {
                $tingkat = $selectedKelas->first()->tingkat_kelas ?? null;
                $allowedKelasIds = $this->getAllowedKelasIdsForRencana($mataPelajaranId, $tingkat)->toArray();
                if (count(array_diff($kelasIds, $allowedKelasIds)) > 0) {
                    return back()->with('error', 'Salah satu kelas tidak termasuk dalam jadwal pelajaran Anda.');
                }
            }

            return view('rencana_pembelajaran.import_preview', [
                'mataPelajaran' => $mataPelajaran,
                'selectedKelas' => $selectedKelas,
                'selectedKelasIds' => $kelasIds,
                'importData' => $data,
                'originalDocxPath' => $docxPath,
                'fileName' => $file->getClientOriginalName(),
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Error saat membaca dokumen: ' . $e->getMessage());
        }
    }

    public function importConfirm(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'original_docx_path' => 'required|string',
            'judul' => 'required|string|max:255',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'metode' => 'nullable|string',
            'media' => 'nullable|string',
            'sumber' => 'nullable|string',
            'alokasi_waktu' => 'nullable|string',
            'praktik_pedagogis' => 'nullable|string',
            'lingkungan_pembelajaran' => 'nullable|string',
            'pemanfaatan_digital' => 'nullable|string',
            'pengalaman_pembelajaran' => 'nullable|string',
            'refleksi_pembelajaran' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'html_content' => 'nullable|string',
        ]);

        if (!\Storage::exists($validated['original_docx_path'])) {
            return back()->with('error', 'File DOCX asli tidak ditemukan di server. Silakan ulangi proses upload.');
        }

        $guru = auth()->user()->guru;
        $mataPelajaranId = $validated['mata_pelajaran_id'];
        $kelasIds = $validated['kelas_ids'];
        $status = $validated['status'];
        $htmlContent = isset($validated['html_content']) ? base64_decode($validated['html_content']) : null;

        if (empty($htmlContent) && \Storage::exists($validated['original_docx_path'])) {
            $htmlContent = $this->convertDocxToHtml(storage_path('app/' . $validated['original_docx_path']));
        }

        if (!$this->isAdminOrKepala()) {
            $tingkat = Kelas::find($kelasIds[0])->tingkat_kelas;
            $allowedKelasIds = $this->getAllowedKelasIdsForRencana($mataPelajaranId, $tingkat)->toArray();
            if (count(array_diff($kelasIds, $allowedKelasIds)) > 0) {
                return back()->with('error', 'Salah satu kelas tidak termasuk dalam jadwal pelajaran Anda.');
            }
        }

        foreach ($kelasIds as $kelasId) {
            $rencana = new RencanaPembelajaran();
            $rencana->guru_id = $guru->id;
            $rencana->mata_pelajaran_id = $mataPelajaranId;
            $rencana->kelas_id = $kelasId;
            $rencana->judul = $validated['judul'];
            $rencana->capaian_pembelajaran = $validated['capaian_pembelajaran'] ?? null;
            $rencana->tujuan = $validated['tujuan'] ?? null;
            $rencana->metode = $validated['metode'] ?? null;
            $rencana->media = $validated['media'] ?? null;
            $rencana->sumber = $validated['sumber'] ?? null;
            $rencana->alokasi_waktu = $validated['alokasi_waktu'] ?? null;
            $rencana->praktik_pedagogis = $validated['praktik_pedagogis'] ?? null;
            $rencana->lingkungan_pembelajaran = $validated['lingkungan_pembelajaran'] ?? null;
            $rencana->pemanfaatan_digital = $validated['pemanfaatan_digital'] ?? null;
            $rencana->pengalaman_pembelajaran = $validated['pengalaman_pembelajaran'] ?? null;
            $rencana->refleksi_pembelajaran = $validated['refleksi_pembelajaran'] ?? null;
            $rencana->penilaian = $validated['penilaian'] ?? null;
            $rencana->status = $status;
            $rencana->html_content = $htmlContent;
            $rencana->original_docx_path = $validated['original_docx_path'];
            $rencana->save();
        }

        $tingkat = Kelas::find($kelasIds[0])->tingkat_kelas;

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', 'Rencana pembelajaran berhasil diimport untuk ' . count($kelasIds) . ' kelas.');
    }

    /**
     * Bulk delete rencana pembelajaran
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|string',
        ]);

        $ids = array_filter(explode(',', $request->input('ids')));
        
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada item yang dipilih');
        }

        $guru = auth()->user()->guru;
        
        // Get first rencana for redirect info
        $firstRencana = RencanaPembelajaran::whereIn('id', $ids)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$firstRencana) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus item ini');
        }

        $mataPelajaranId = $firstRencana->mata_pelajaran_id;
        $tingkat = $firstRencana->kelas->tingkat_kelas;
        $deletedCount = 0;

        // Delete only rencana that belong to this guru
        $deletedCount = RencanaPembelajaran::whereIn('id', $ids)
            ->where('guru_id', $guru->id)
            ->delete();

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', $deletedCount . ' rencana pembelajaran berhasil dihapus.');
    }

    /**
     * Parse Word document content and extract data
     */
    private function parseWordDocument($text)
    {
        $data = [];
        $text = preg_replace('/\r\n?/', "\n", $text);
        $text = trim($text);

        $lines = array_filter(array_map('trim', explode("\n", $text)), fn($line) => $line !== '');

        $headingMap = [
            'JUDUL' => 'judul',
            'MATA PELAJARAN' => 'mata_pembelajaran',
            'KELAS' => 'kelas',
            'STATUS' => 'status',
            'DESKRIPSI' => 'capaian_pembelajaran',
            'DESKRIPSI / CAPAIAN PEMBELAJARAN' => 'capaian_pembelajaran',
            'CAPAIAN PEMBELAJARAN' => 'capaian_pembelajaran',
            'TUJUAN PEMBELAJARAN' => 'tujuan',
            'TUJUAN' => 'tujuan',
            'METODE PEMBELAJARAN' => 'metode',
            'METODE' => 'metode',
            'MEDIA PEMBELAJARAN' => 'media',
            'MEDIA' => 'media',
            'SUMBER BELAJAR' => 'sumber',
            'SUMBER' => 'sumber',
            'ALOKASI WAKTU' => 'alokasi_waktu',
            'PRAKTIK PEDAGOGIS' => 'praktik_pedagogis',
            'PRAKTIK PEDAGOGI' => 'praktik_pedagogis',
            'LINGKUNGAN PEMBELAJARAN' => 'lingkungan_pembelajaran',
            'PEMANFAATAN DIGITAL' => 'pemanfaatan_digital',
            'PENGALAMAN PEMBELAJARAN' => 'pengalaman_pembelajaran',
            'REFLEKSI PEMBELAJARAN' => 'refleksi_pembelajaran',
            'PENILAIAN' => 'penilaian',
        ];

        $currentSection = null;

        foreach ($lines as $line) {
            $upperLine = preg_replace('/\s+/', ' ', strtoupper($line));
            $matched = false;

            foreach ($headingMap as $heading => $field) {
                if ($upperLine === $heading) {
                    $currentSection = $field;
                    $matched = true;
                    break;
                }

                if (preg_match('/^' . preg_quote($heading, '/') . '\s*[:\-\|]+\s*(.*)$/i', $line, $matches)) {
                    $currentSection = $field;
                    $value = trim($matches[1]);
                    if ($value !== '') {
                        $data[$currentSection] = $value;
                    }
                    $matched = true;
                    break;
                }

                if (strpos($upperLine, $heading . ' |') === 0) {
                    $currentSection = $field;
                    $value = trim(substr($line, strlen($heading) + 1));
                    if ($value !== '') {
                        $data[$currentSection] = $value;
                    }
                    $matched = true;
                    break;
                }
            }

            if ($matched) {
                continue;
            }

            if (strpos($line, ':') !== false || strpos($line, '-') !== false) {
                $parts = preg_split('/\s*[:\-]\s*/', $line, 2);
                if (count($parts) === 2) {
                    $left = trim($parts[0]);
                    $right = trim($parts[1]);
                    $upperLeft = preg_replace('/\s+/', ' ', strtoupper($left));
                    if (isset($headingMap[$upperLeft])) {
                        $data[$headingMap[$upperLeft]] = $right;
                        $currentSection = null;
                        continue;
                    }
                }
            }

            if (strpos($line, '|') !== false) {
                [$left, $right] = array_map('trim', explode('|', $line, 2));
                $upperLeft = preg_replace('/\s+/', ' ', strtoupper($left));
                if (isset($headingMap[$upperLeft])) {
                    $data[$headingMap[$upperLeft]] = $right;
                    $currentSection = null;
                    continue;
                }
            }

            if ($currentSection) {
                $data[$currentSection] = isset($data[$currentSection])
                    ? trim($data[$currentSection] . "\n" . $line)
                    : $line;
            }
        }

        if (empty($data['judul'])) {
            foreach ($lines as $line) {
                $upper = preg_replace('/\s+/', ' ', strtoupper($line));
                if (stripos($upper, 'TEMPLATE RENCANA PEMBELAJARAN') !== false) {
                    continue;
                }
                if (preg_match('/^(JUDUL|MATA PELAJARAN|KELAS|STATUS|DESKRIPSI|CAPAIAN PEMBELAJARAN|TUJUAN PEMBELAJARAN|TUJUAN|METODE PEMBELAJARAN|MEDIA PEMBELAJARAN|SUMBER BELAJAR|SUMBER|ALOKASI WAKTU|PRAKTIK PEDAGOGIS|LINGKUNGAN PEMBELAJARAN|PEMANFAATAN DIGITAL|PENGALAMAN PEMBELAJARAN|REFLEKSI PEMBELAJARAN|PENILAIAN)\b/', $upper)) {
                    continue;
                }
                if (strlen($line) > 5) {
                    $data['judul'] = $line;
                    break;
                }
            }
        }

        return $data;
    }

    private function convertDocxToHtml(string $filePath)
    {
        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');
            ob_start();
            $writer->save('php://output');
            return ob_get_clean();
        } catch (\Throwable $e) {
            return '<div class="alert alert-warning">Preview HTML tidak tersedia karena gagal mengonversi DOCX: ' . e($e->getMessage()) . '</div>';
        }
    }

    private function renderHtmlPreview(array $data)
    {
        $html = '<div class="rpp-preview">';

        if (!empty($data['judul'])) {
            $html .= '<h2>' . e($data['judul']) . '</h2>';
        }

        $html .= '<table class="table table-bordered"><tbody>';
        foreach (['mata_pembelajaran' => 'Mata Pelajaran', 'kelas' => 'Kelas', 'status' => 'Status'] as $field => $label) {
            if (!empty($data[$field])) {
                $html .= '<tr><th>' . e($label) . '</th><td>' . e($data[$field]) . '</td></tr>';
            }
        }
        $html .= '</tbody></table>';

        $sections = [
            'capaian_pembelajaran' => 'Deskripsi / Capaian Pembelajaran',
            'tujuan' => 'Tujuan Pembelajaran',
            'metode' => 'Metode Pembelajaran',
            'media' => 'Media Pembelajaran',
            'sumber' => 'Sumber Belajar',
            'alokasi_waktu' => 'Alokasi Waktu',
            'praktik_pedagogis' => 'Praktik Pedagogis',
            'lingkungan_pembelajaran' => 'Lingkungan Pembelajaran',
            'pemanfaatan_digital' => 'Pemanfaatan Digital',
            'pengalaman_pembelajaran' => 'Pengalaman Pembelajaran',
            'refleksi_pembelajaran' => 'Refleksi Pembelajaran',
            'penilaian' => 'Penilaian',
        ];

        foreach ($sections as $field => $label) {
            if (!empty($data[$field])) {
                $content = nl2br(e($data[$field]));
                $html .= '<h4>' . e($label) . '</h4>';
                $html .= '<div>' . $content . '</div>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    private function extractWordElementText($element)
    {
        if (method_exists($element, 'getText')) {
            return trim($element->getText());
        }

        if (method_exists($element, 'getElements')) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $childText = $this->extractWordElementText($child);
                if ($childText !== '') {
                    $text .= ($text === '' ? '' : ' ') . $childText;
                }
            }
            return trim($text);
        }

        return '';
    }
}
