<?php
namespace App\Http\Controllers;

require_once app_path('Helpers/GuruUserHelper.php');

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

class AscTimetableController extends Controller
{
    public function index()
    {
        return view('asc_timetable.index');
    }

    public function parseXml(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|file|mimes:xml|max:10240', // max 10MB
        ]);

        try {
            $file = $request->file('xml_file');
            $xmlContent = file_get_contents($file->getRealPath());
            
            // Load XML
            $xml = new SimpleXMLElement($xmlContent);
            
            $preview = [
                'periods' => [],
                'subjects' => [],
                'teachers' => [],
                'classes' => [],
                'lessons' => [],
                'dayDefs' => []
            ];

            // Parse day definitions for mapping
            if (isset($xml->daysdefs->daysdef)) {
                foreach ($xml->daysdefs->daysdef as $dayDef) {
                    $preview['dayDefs'][(string)$dayDef['id']] = [
                        'name' => (string)$dayDef['name'],
                        'short' => (string)$dayDef['short']
                    ];
                }
            }

            // Parse Periods (Jam Belajar)
            if (isset($xml->periods->period)) {
                foreach ($xml->periods->period as $period) {
                    $preview['periods'][] = [
                        'urutan' => (int)$period['period'],
                        'hari' => 'Semua Hari',
                        'jam_mulai' => (string)$period['starttime'],
                        'jam_selesai' => (string)$period['endtime'],
                        'jenis' => 'Reguler',
                        'nama' => (string)$period['name']
                    ];
                }
            }

            // Parse Subjects (Mata Pelajaran)
            if (isset($xml->subjects->subject)) {
                foreach ($xml->subjects->subject as $subject) {
                    $kode = (string)$subject['short'];
                    $nama = (string)$subject['name'];
                    
                    $exists = DB::table('mata_pelajaran')
                        ->where('kode_mapel', $kode)
                        ->exists();

                    $preview['subjects'][] = [
                        'kode_mapel' => $kode,
                        'nama_mapel' => $nama,
                        'status' => $exists ? 'exists' : 'new'
                    ];
                }
            }

            // Parse Teachers (Guru)
            if (isset($xml->teachers->teacher)) {
                foreach ($xml->teachers->teacher as $teacher) {
                    $kodeGuru = (string)$teacher['short'];
                    $nama = (string)$teacher['name'];
                    
                    // Check if exists by kode_guru or nama
                    $existingByKode = DB::table('guru')
                        ->where('kode_guru', $kodeGuru)
                        ->first();
                    
                    $existingByNama = DB::table('guru')
                        ->where('nama', $nama)
                        ->first();

                    $status = 'new';
                    $duplicate_type = null;
                    $existing_id = null;
                    
                    if ($existingByKode) {
                        $status = 'exists_kode';
                        $duplicate_type = 'kode_guru';
                        $existing_id = $existingByKode->id;
                    } elseif ($existingByNama) {
                        $status = 'exists_nama';
                        $duplicate_type = 'nama';
                        $existing_id = $existingByNama->id;
                    }

                    $preview['teachers'][] = [
                        'kode_guru' => $kodeGuru,
                        'nama' => $nama,
                        'email' => null,
                        'no_hp' => null,
                        'jenis_kelamin' => (string)$teacher['gender'] === 'M' ? 'L' : 'P',
                        'status' => $status,
                        'duplicate_type' => $duplicate_type,
                        'existing_id' => $existing_id,
                        'existing_data' => $existingByKode ?: $existingByNama
                    ];
                }
            }

            // Parse Classes (Kelas)
            if (isset($xml->classes->class)) {
                foreach ($xml->classes->class as $class) {
                    $kode = (string)$class['short'];
                    $nama = (string)$class['name'];
                    
                    // Detect tingkat from class name
                    $tingkat = $this->extractTingkatKelas($nama);
                    
                    $exists = DB::table('kelas')
                        ->where('nama_kelas', $nama)
                        ->exists();

                    $preview['classes'][] = [
                        'nama_kelas' => $nama,
                        'kode_kelas' => $kode,
                        'tingkat_kelas' => $tingkat,
                        'jurusan' => 'Umum',
                        'status' => $exists ? 'exists' : 'new'
                    ];
                }
            }

            // Parse Lessons (Jadwal KBM) - all lessons
            // First, build ID to code/name mapping
            $classIdMap = [];
            $teacherIdMap = [];
            $subjectIdMap = [];
            
            if (isset($xml->classes->class)) {
                foreach ($xml->classes->class as $class) {
                    $classIdMap[(string)$class['id']] = [
                        'kode' => (string)$class['short'],
                        'nama' => (string)$class['name']
                    ];
                }
            }
            
            if (isset($xml->teachers->teacher)) {
                foreach ($xml->teachers->teacher as $teacher) {
                    $teacherIdMap[(string)$teacher['id']] = [
                        'kode' => (string)$teacher['short'],
                        'nama' => (string)$teacher['name']
                    ];
                }
            }
            
            if (isset($xml->subjects->subject)) {
                foreach ($xml->subjects->subject as $subject) {
                    $subjectIdMap[(string)$subject['id']] = [
                        'kode' => (string)$subject['short'],
                        'nama' => (string)$subject['name']
                    ];
                }
            }

            // Build lesson ID map for cards
            $lessonMap = [];
            if (isset($xml->lessons->lesson)) {
                foreach ($xml->lessons->lesson as $lesson) {
                    $lessonId = (string)$lesson['id'];
                    $lessonMap[$lessonId] = [
                        'classids' => (string)$lesson['classids'],
                        'teacherids' => (string)$lesson['teacherids'],
                        'subjectid' => (string)$lesson['subjectid'],
                        'daysdefid' => (string)$lesson['daysdefid'],
                        'periodsperweek' => (float)($lesson['periodsperweek'] ?? 0)
                    ];
                }
            }

            // Build all possible grid slot for preview: hari, kelas, jam_ke
            // 1. Ambil semua hari dari dayNames
            $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
            // 2. Ambil semua kelas dari <classes> di XML, pastikan seluruh kelas diambil
            $allKelas = [];
            if (isset($xml->classes->class)) {
                foreach ($xml->classes->class as $class) {
                    $kode = (string)$class['short'];
                    if (!in_array($kode, $allKelas)) {
                        $allKelas[] = $kode;
                    }
                }
            }
            // 3. Ambil semua urutan period dari <periods>
            $allPeriods = [];
            if (isset($xml->periods->period)) {
                foreach ($xml->periods->period as $period) {
                    $allPeriods[] = (int)$period['period'];
                }
            }
            sort($allPeriods);

            // 4. Index semua pelajaran dari <cards> ke [hari][kelas][jam_ke]
            $lessonGrid = [];
            if (isset($xml->cards->card)) {
                foreach ($xml->cards->card as $card) {
                    $lessonId = (string)$card['lessonid'];
                    $periodNumber = (int)($card['period'] ?? 1);
                    $days = (string)($card['days'] ?? '00000');
                    if (!isset($lessonMap[$lessonId])) continue;
                    $lesson = $lessonMap[$lessonId];
                    $kelasInfo = $classIdMap[$lesson['classids']] ?? null;
                    $guruInfo = $teacherIdMap[$lesson['teacherids']] ?? null;
                    $mapelInfo = $subjectIdMap[$lesson['subjectid']] ?? null;
                    if (!$kelasInfo || !$guruInfo || !$mapelInfo) continue;
                    for ($i = 0; $i < 5; $i++) {
                        if (isset($days[$i]) && $days[$i] === '1') {
                            $dayName = $dayNames[$i];
                            $lessonGrid[$dayName][$kelasInfo['kode']][$periodNumber] = [
                                'kelas_kode' => $kelasInfo['kode'],
                                'kelas_nama' => $kelasInfo['nama'],
                                'guru_nip' => $guruInfo['kode'],
                                'guru_nama' => $guruInfo['nama'],
                                'mapel_kode' => $mapelInfo['kode'],
                                'mapel_nama' => $mapelInfo['nama'],
                                'hari' => $dayName,
                                'jam_ke' => $periodNumber,
                                'jp' => $lesson['periodsperweek'],
                                'classIdAttr' => $lesson['classids'],
                                'teacherIdAttr' => $lesson['teacherids'],
                                'subjectIdAttr' => $lesson['subjectid']
                            ];
                        }
                    }
                }
            }

            // 5. Bangun preview['lessons'] untuk seluruh slot grid (hari, kelas, jam_ke)
            // Selalu tampilkan grid lengkap Senin-Jumat, semua kelas, semua jam ke
            foreach ($dayNames as $dayName) {
                foreach ($allKelas as $kelasKode) {
                    foreach ($allPeriods as $jamKe) {
                        if (isset($lessonGrid[$dayName][$kelasKode][$jamKe])) {
                            $preview['lessons'][] = $lessonGrid[$dayName][$kelasKode][$jamKe];
                        } else {
                            $preview['lessons'][] = [
                                'kelas_kode' => $kelasKode,
                                'kelas_nama' => '',
                                'guru_nip' => '',
                                'guru_nama' => '',
                                'mapel_kode' => '',
                                'mapel_nama' => '',
                                'hari' => $dayName,
                                'jam_ke' => $jamKe,
                                'jp' => '',
                                'classIdAttr' => '',
                                'teacherIdAttr' => '',
                                'subjectIdAttr' => ''
                            ];
                        }
                    }
                }
            }


            // Store in session for later import
            session(['xml_import_data' => $xmlContent]);

            return view('asc_timetable.preview', compact('preview'));

        } catch (\Exception $e) {
            Log::error('ASC Timetable Parse Error: ' . $e->getMessage());
            
            return redirect()->route('asc_timetable.index')
                ->with('error', 'Gagal parse XML: ' . $e->getMessage());
        }
    }

    public function confirmImport(Request $request)
    {
        try {
            $xmlContent = session('xml_import_data');
            
            if (!$xmlContent) {
                return redirect()->route('asc_timetable.index')
                    ->with('error', 'Session expired. Silakan upload file XML lagi.');
            }

            // Get selected import types
            $importTypes = $request->input('import_types', []);
            
            if (empty($importTypes)) {
                return redirect()->route('asc_timetable.index')
                    ->with('error', 'Silakan pilih minimal satu jenis data untuk diimport.');
            }

            DB::beginTransaction();

            $xml = new SimpleXMLElement($xmlContent);
            
            $stats = [
                'periods' => 0,
                'subjects' => 0,
                'teachers' => 0,
                'classes' => 0,
                'lessons' => 0,
                'tugas_guru' => 0,
                'errors' => []
            ];

            // First, build ID mapping for classes to extract tingkat
            $classIdMap = [];
            if (isset($xml->classes->class)) {
                foreach ($xml->classes->class as $class) {
                    $nama = (string)$class['name'];
                    $tingkat = $this->extractTingkatKelas($nama);
                    $classIdMap[(string)$class['id']] = [
                        'nama' => $nama,
                        'tingkat' => $tingkat
                    ];
                }
            }

            // Build subject ID mapping
            $subjectIdMap = [];
            if (isset($xml->subjects->subject)) {
                foreach ($xml->subjects->subject as $subject) {
                    $subjectIdMap[(string)$subject['id']] = [
                        'kode' => (string)$subject['short'],
                        'nama' => (string)$subject['name']
                    ];
                }
            }

            // Calculate JP per subject per tingkat from lessons
            // Take the first occurrence of each subject in each tingkat (since all classes in same tingkat have same JP)
            $jpByMapelAndTingkat = [];
            if (isset($xml->lessons->lesson)) {
                foreach ($xml->lessons->lesson as $lesson) {
                    $classIdAttr = (string)$lesson['classids'];
                    $subjectIdAttr = (string)$lesson['subjectid'];
                    $periodsPerWeek = (float)$lesson['periodsperweek'] ?? 0;
                    
                    if (!isset($classIdMap[$classIdAttr]) || !isset($subjectIdMap[$subjectIdAttr])) {
                        continue;
                    }
                    
                    $tingkat = $classIdMap[$classIdAttr]['tingkat'];
                    $mapelKode = $subjectIdMap[$subjectIdAttr]['kode'];
                    
                    $key = $mapelKode . '|' . $tingkat;
                    // Only set if not already set (take the first occurrence)
                    if (!isset($jpByMapelAndTingkat[$key])) {
                        $jpByMapelAndTingkat[$key] = (int)$periodsPerWeek;
                    }
                }
            }

            // Import Subjects (Mata Pelajaran) first
            if (in_array('subjects', $importTypes) && isset($xml->subjects->subject)) {
                foreach ($xml->subjects->subject as $subject) {
                    $subjectData = [
                        'kode_mapel' => (string)$subject['short'],
                        'nama_mapel' => (string)$subject['name'],
                    ];

                    $exists = DB::table('mata_pelajaran')
                        ->where('kode_mapel', $subjectData['kode_mapel'])
                        ->exists();

                    if (!$exists) {
                        DB::table('mata_pelajaran')->insert($subjectData);
                        $stats['subjects']++;
                    }
                }
                
                // After importing subjects, add them to kurikulum_mapel for each tingkat with calculated JP
                $allSubjects = DB::table('mata_pelajaran')->get();
                $tingkatList = ['X', 'XI', 'XII'];
                $jurusanList = ['Umum'];
                
                foreach ($allSubjects as $mapel) {
                    foreach ($tingkatList as $tingkat) {
                        foreach ($jurusanList as $jurusan) {
                            // Check if already exists
                            $kurikExist = DB::table('kurikulum_mapel')
                                ->where('tingkat', $tingkat)
                                ->where('jurusan', $jurusan)
                                ->where('mata_pelajaran_id', $mapel->id)
                                ->exists();
                            
                            // Get JP from calculated data
                            $key = $mapel->kode_mapel . '|' . $tingkat;
                            $jp = isset($jpByMapelAndTingkat[$key]) ? (int)$jpByMapelAndTingkat[$key] : 0;
                            
                            // If not exists, insert with calculated jp
                            if (!$kurikExist) {
                                DB::table('kurikulum_mapel')->insert([
                                    'tingkat' => $tingkat,
                                    'jurusan' => $jurusan,
                                    'mata_pelajaran_id' => $mapel->id,
                                    'jp' => $jp,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            } elseif ($jp > 0) {
                                // If exists and we have JP data, update the JP value
                                DB::table('kurikulum_mapel')
                                    ->where('tingkat', $tingkat)
                                    ->where('jurusan', $jurusan)
                                    ->where('mata_pelajaran_id', $mapel->id)
                                    ->update(['jp' => $jp, 'updated_at' => now()]);
                            }
                        }
                    }
                }
            }

            // Import Teachers (Guru)
            if (in_array('teachers', $importTypes) && isset($xml->teachers->teacher)) {
                // Get teacher actions from request
                $teacherActions = $request->input('teacher_action', []);
                $teacherKodes = $request->input('teacher_kode', []);
                $teacherNamas = $request->input('teacher_nama', []);
                $teacherGenders = $request->input('teacher_gender', []);
                $teacherExistingIds = $request->input('teacher_existing_id', []);

                foreach ($xml->teachers->teacher as $index => $teacher) {
                    $kodeGuru = (string)$teacher['short'];
                    $namaGuru = (string)$teacher['name'];
                    $jenisKelamin = ((string)$teacher['gender'] === 'M') ? 'L' : 'P';
                    $action = $teacherActions[$index] ?? null;
                    $existingId = $teacherExistingIds[$index] ?? null;
                    $existsByKode = DB::table('guru')->where('kode_guru', $kodeGuru)->first();
                    $existsByNama = DB::table('guru')->where('nama', $namaGuru)->first();

                    if ($existsByKode || $existsByNama) {
                        if ($action === 'replace' && $existingId) {
                            DB::table('guru')
                                ->where('id', $existingId)
                                ->update([
                                    'kode_guru' => $kodeGuru,
                                    'nama' => $namaGuru,
                                    'jenis_guru' => 'Pengajar',
                                    'jenis_kelamin' => $jenisKelamin,
                                    'updated_at' => now()
                                ]);
                            $stats['teachers']++;
                        } elseif ($action === 'add_new') {
                            $kodeGuruBaru = $kodeGuru . '_' . time();
                            createGuruAndUser($kodeGuruBaru, $namaGuru, $jenisKelamin);
                            $stats['teachers']++;
                        }
                        // skip jika null
                    } else {
                        createGuruAndUser($kodeGuru, $namaGuru, $jenisKelamin);
                        $stats['teachers']++;
                    }


                }
            }

            // Import Classes (Kelas)
            if (in_array('classes', $importTypes) && isset($xml->classes->class)) {
                foreach ($xml->classes->class as $class) {
                    $nama = (string)$class['name'];
                    
                    // Detect tingkat from class name
                    $tingkat = $this->extractTingkatKelas($nama);
                    
                    $classData = [
                        'nama_kelas' => $nama,
                        'tingkat_kelas' => $tingkat,
                        'jurusan' => 'Umum',
                    ];

                    $exists = DB::table('kelas')
                        ->where('nama_kelas', $classData['nama_kelas'])
                        ->exists();

                    if (!$exists) {
                        DB::table('kelas')->insert($classData);
                        $stats['classes']++;
                    }
                }
            }

            // Import Periods (Jam Belajar) - Create for each day
            if (in_array('periods', $importTypes) && isset($xml->periods->period)) {
                $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
                
                foreach ($xml->periods->period as $period) {
                    $urutan = (int)$period['period'];
                    $jamMulai = (string)$period['starttime'];
                    $jamSelesai = (string)$period['endtime'];
                    
                    foreach ($days as $hari) {
                        $exists = DB::table('jam_belajar')
                            ->where('hari', $hari)
                            ->where('urutan', $urutan)
                            ->exists();

                        if (!$exists) {
                            DB::table('jam_belajar')->insert([
                                'hari' => $hari,
                                'urutan' => $urutan,
                                'jam_mulai' => $jamMulai,
                                'jam_selesai' => $jamSelesai,
                                'jenis' => 'KBM',
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $stats['periods']++;
                        }
                    }
                }
            }

            // Parse day definitions for mapping
            $dayDefsMap = [];
            if (isset($xml->daysdefs->daysdef)) {
                foreach ($xml->daysdefs->daysdef as $dayDef) {
                    $dayDefsMap[(string)$dayDef['id']] = (string)$dayDef['name'];
                }
            }

            // Get active tahun ajaran and semester
            $tahunAjaranId = DB::table('tahun_ajaran')->where('is_active', true)->value('id');
            $semesterId = DB::table('semester')->where('is_active', true)->value('id');
            
            // Fallback if no active tahun ajaran or semester
            if (!$tahunAjaranId) {
                $tahunAjaranId = DB::table('tahun_ajaran')->orderByDesc('id')->value('id');
            }
            if (!$semesterId) {
                $semesterId = DB::table('semester')->orderByDesc('id')->value('id');
            }

            // Import Lessons (Jadwal KBM)
            if (in_array('lessons', $importTypes)) {
                // Use session data which has already been parsed and mapped
                $xmlImportData = session('xml_import_data');
                if ($xmlImportData) {
                    $xml = simplexml_load_string($xmlImportData);

                    // Build ID to code/name mapping again (same as in parseXml)
                    $classIdMap = [];
                    $teacherIdMap = [];
                    $subjectIdMap = [];

                    if (isset($xml->classes->class)) {
                        foreach ($xml->classes->class as $class) {
                            $classIdMap[(string)$class['id']] = [
                                'kode' => (string)$class['short'],
                                'nama' => (string)$class['name']
                            ];
                        }
                    }

                    if (isset($xml->teachers->teacher)) {
                        foreach ($xml->teachers->teacher as $teacher) {
                            $teacherIdMap[(string)$teacher['id']] = [
                                'kode' => (string)$teacher['short'],
                                'nama' => (string)$teacher['name']
                            ];
                        }
                    }

                    if (isset($xml->subjects->subject)) {
                        foreach ($xml->subjects->subject as $subject) {
                            $subjectIdMap[(string)$subject['id']] = [
                                'kode' => (string)$subject['short'],
                                'nama' => (string)$subject['name']
                            ];
                        }
                    }

                    // Build lesson ID mapping
                    $lessonMap = [];
                    if (isset($xml->lessons->lesson)) {
                        foreach ($xml->lessons->lesson as $lesson) {
                            $lessonId = (string)$lesson['id'];
                            $lessonMap[$lessonId] = [
                                'classids' => (string)$lesson['classids'],
                                'teacherids' => (string)$lesson['teacherids'],
                                'subjectid' => (string)$lesson['subjectid'],
                            ];
                        }
                    }

                    // Import from cards (actual schedule with period numbers)
                    if (isset($xml->cards->card)) {
                        $dayNames = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

                        foreach ($xml->cards->card as $card) {
                            try {
                                $lessonId = (string)$card['lessonid'];
                                $periodNumber = (int)($card['period'] ?? 1);
                                $days = (string)($card['days'] ?? '00000');

                                if (!isset($lessonMap[$lessonId])) {
                                    continue;
                                }

                                $lesson = $lessonMap[$lessonId];

                                // Get info from mapping
                                $kelasInfo = $classIdMap[$lesson['classids']] ?? null;
                                $guruInfo = $teacherIdMap[$lesson['teacherids']] ?? null;
                                $mapelInfo = $subjectIdMap[$lesson['subjectid']] ?? null;

                                if (!$kelasInfo || !$guruInfo || !$mapelInfo) {
                                    continue;
                                }

                                // Get IDs from database
                                $kelasId = DB::table('kelas')
                                    ->whereRaw('LOWER(TRIM(nama_kelas)) = ?', [strtolower(trim($kelasInfo['nama']))])
                                    ->value('id');
                                $guruId = DB::table('guru')
                                    ->whereRaw('LOWER(TRIM(kode_guru)) = ?', [strtolower(trim($guruInfo['kode']))])
                                    ->value('id');
                                $mapelId = DB::table('mata_pelajaran')
                                    ->whereRaw('LOWER(TRIM(kode_mapel)) = ?', [strtolower(trim($mapelInfo['kode']))])
                                    ->value('id');

                                if (!$kelasId || !$guruId || !$mapelId) {
                                    $notFound = [];
                                    if (!$kelasId) $notFound[] = 'Kelas: ' . $kelasInfo['nama'];
                                    if (!$guruId) $notFound[] = 'Guru: ' . $guruInfo['kode'];
                                    if (!$mapelId) $notFound[] = 'Mapel: ' . $mapelInfo['kode'];
                                    Log::warning('Import KBM: Data tidak ditemukan: ' . implode(', ', $notFound));
                                    continue;
                                }

                                // Decode days bitmap: "10000"=Senin, "01000"=Selasa, etc.
                                for ($i = 0; $i < 5; $i++) {
                                    if (isset($days[$i]) && $days[$i] === '1') {
                                        $hari = $dayNames[$i];

                                        // Ambil jam_belajar KBM dari database sesuai hari dan urutan

                                        $jamBelajar = DB::table('jam_belajar')
                                            ->where('hari', $hari)
                                            ->where('urutan', $periodNumber)
                                            ->first();

                                        // Abaikan jika slot bukan KBM (misal ISTIRAHAT, UPACARA, dll)
                                        if (!$jamBelajar || strtoupper(trim($jamBelajar->jenis)) !== 'KBM') {
                                            continue;
                                        }

                                        // Check if jadwal_kbm sudah ada di slot ini
                                        $exists = DB::table('jadwal_kbm')
                                            ->where('kelas_id', $kelasId)
                                            ->where('guru_id', $guruId)
                                            ->where('mata_pelajaran_id', $mapelId)
                                            ->where('hari', $hari)
                                            ->where('jam_ke', $periodNumber)
                                            ->where('tahun_ajaran_id', $tahunAjaranId)
                                            ->where('semester_id', $semesterId)
                                            ->exists();

                                        if (!$exists) {
                                            DB::table('jadwal_kbm')->insert([
                                                'kelas_id' => $kelasId,
                                                'guru_id' => $guruId,
                                                'mata_pelajaran_id' => $mapelId,
                                                'jam_belajar_id' => $jamBelajar->id,
                                                'hari' => $hari,
                                                'jam_ke' => $periodNumber,
                                                'tahun_ajaran_id' => $tahunAjaranId,
                                                'semester_id' => $semesterId,
                                                'created_at' => now(),
                                                'updated_at' => now()
                                            ]);
                                            $stats['lessons']++;
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                $stats['errors'][] = 'Error importing card: ' . $e->getMessage();
                                Log::error('Card import error: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

            // Auto-generate Tugas Guru from imported Jadwal KBM
            if (in_array('lessons', $importTypes)) {
                $tugasGuruCount = 0;
                
                // Get unique guru-mapel-tingkat combinations from jadwal_kbm
                $tugasData = DB::table('jadwal_kbm as jk')
                    ->join('kelas as k', 'jk.kelas_id', '=', 'k.id')
                    ->where('jk.tahun_ajaran_id', $tahunAjaranId)
                    ->where('jk.semester_id', $semesterId)
                    ->select(
                        'jk.guru_id',
                        'jk.mata_pelajaran_id',
                        'k.tingkat_kelas',
                        DB::raw('GROUP_CONCAT(DISTINCT jk.kelas_id) as kelas_ids')
                    )
                    ->groupBy('jk.guru_id', 'jk.mata_pelajaran_id', 'k.tingkat_kelas')
                    ->get();

                foreach ($tugasData as $tugas) {
                    // Skip if tingkat_kelas is null
                    if (empty($tugas->tingkat_kelas)) {
                        continue;
                    }

                    // Check if kelas_ids contains multiple classes for this tingkat
                    $kelasIds = explode(',', $tugas->kelas_ids);
                    
                    // If guru teaches this subject to all classes in a tingkat, create one entry without specific kelas_id
                    // Otherwise create separate entries for each kelas
                    $kelasCountForTingkat = DB::table('kelas')
                        ->where('tingkat_kelas', $tugas->tingkat_kelas)
                        ->count();
                    
                    if (count($kelasIds) >= $kelasCountForTingkat) {
                        // Create entry for all classes in tingkat
                        DB::table('tugas_guru')->updateOrCreate(
                            [
                                'guru_id' => $tugas->guru_id,
                                'mata_pelajaran_id' => $tugas->mata_pelajaran_id,
                                'tingkat_kelas' => $tugas->tingkat_kelas,
                                'kelas_id' => null
                            ],
                            [
                                'is_active' => 1,
                                'keterangan' => 'Auto-generated from jadwal import',
                                'updated_at' => now()
                            ]
                        );
                        $tugasGuruCount++;
                    } else {
                        // Create separate entry for each kelas
                        foreach ($kelasIds as $kelasId) {
                            DB::table('tugas_guru')->updateOrCreate(
                                [
                                    'guru_id' => $tugas->guru_id,
                                    'mata_pelajaran_id' => $tugas->mata_pelajaran_id,
                                    'tingkat_kelas' => $tugas->tingkat_kelas,
                                    'kelas_id' => $kelasId
                                ],
                                [
                                    'is_active' => 1,
                                    'keterangan' => 'Auto-generated from jadwal import',
                                    'updated_at' => now()
                                ]
                            );
                            $tugasGuruCount++;
                        }
                    }
                }
                
                $stats['tugas_guru'] = $tugasGuruCount;
            }

            DB::commit();
            
            // Clear session
            session()->forget('xml_import_data');

            return redirect()->route('asc_timetable.index')
                ->with('success', sprintf(
                    'Import berhasil! Jam Belajar: %d, Mata Pelajaran: %d, Guru: %d, Kelas: %d, Jadwal: %d, Tugas Guru: %d',
                    $stats['periods'],
                    $stats['subjects'],
                    $stats['teachers'],
                    $stats['classes'],
                    $stats['lessons'],
                    $stats['tugas_guru'] ?? 0
                ));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ASC Timetable Import Error: ' . $e->getMessage());
            
            return redirect()->route('asc_timetable.index')
                ->with('error', 'Gagal import XML: ' . $e->getMessage());
        }
    }

    private function convertDayName($dayName)
    {
        // Map from aSc day names to Indonesian day names
        $dayMap = [
            'Senin' => 'Senin',
            'Selasa' => 'Selasa',
            'Rabu' => 'Rabu',
            'Kamis' => 'Kamis',
            'Jumat' => 'Jumat',
            'Sabtu' => 'Sabtu',
            'Minggu' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        foreach ($dayMap as $key => $value) {
            if (stripos($dayName, $key) !== false) {
                return $value;
            }
        }

        return 'Senin'; // default
    }

    private function extractTingkatKelas($nama)
    {
        // Extract tingkat from class name
        // Check for 12 or XII first (longest match)
        if (preg_match('/\b(12|XII|xii)\b/i', $nama)) {
            return 'XII';
        }
        // Check for 11 or XI
        if (preg_match('/\b(11|XI|xi)\b/i', $nama)) {
            return 'XI';
        }
        // Check for 10 or X
        if (preg_match('/\b(10|X|x)\b/i', $nama)) {
            return 'X';
        }
        
        // Default to X if cannot determine
        return 'X';
    }

    public function downloadTemplate()
    {
        $file = public_path('../template/SEMESTER_2.xml');
        
        if (!file_exists($file)) {
            abort(404, 'Template file not found');
        }

        return response()->download($file, 'ASC_Timetable_Template.xml');
    }
}
