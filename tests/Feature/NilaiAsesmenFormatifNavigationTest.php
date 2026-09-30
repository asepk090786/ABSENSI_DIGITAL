<?php

namespace Tests\Feature;

use App\Exports\NilaiInputGridTemplateExport;
use App\Imports\NilaiInputGridImport;
use Tests\TestCase;

class NilaiAsesmenFormatifNavigationTest extends TestCase
{
    public function test_asesmen_formatif_links_preserve_selected_filters(): void
    {
        $contents = file_get_contents(base_path('resources/views/nilai/asesmen_formatif.blade.php'));

        $this->assertStringContainsString('array_filter([', $contents);
        $this->assertStringContainsString("'kelas_id' => \$selectedKelasId", $contents);
        $this->assertStringContainsString("'mapel_id' => \$selectedMapelId", $contents);
    }

    public function test_value_detail_view_has_priority_over_summary_table(): void
    {
        $contents = file_get_contents(base_path('resources/views/nilai/index.blade.php'));

        $this->assertStringContainsString('(!($isViewMode ?? false)) && !$showInputForm && $kelasTugasTable->isNotEmpty()', $contents);
    }

    public function test_value_columns_only_include_components_in_filtered_rows(): void
    {
        $contents = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));
        $columnsSection = substr($contents, strpos($contents, '$assessmentGroups = $items'), 1500);

        $this->assertStringContainsString("->groupBy(function (\$item)", $columnsSection);
        $this->assertStringContainsString("'key' => \$item->assessment_uuid", $columnsSection);
        $this->assertStringNotContainsString('$komponenList', $columnsSection);
    }

    public function test_selected_assessment_component_filter_includes_daily_scores(): void
    {
        $contents = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        $this->assertStringContainsString("'komponen_id' => \$validated['komponen_id'] ?? 0", $contents);
        $this->assertStringContainsString("(string) \$selectedKomponenId === '0'", $contents);
        $this->assertStringContainsString("whereNull('nilai_harian.komponen_id')", $contents);
    }

    public function test_each_created_assessment_gets_a_separate_identity(): void
    {
        $contents = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        $this->assertStringContainsString('$assessmentUuid = (string) Str::uuid();', $contents);
        $this->assertStringContainsString("'assessment_uuid' => \$assessmentUuid", $contents);
        $this->assertStringContainsString("'nh.assessment_uuid'", $contents);
    }

    public function test_formative_edit_has_a_return_link_and_hides_add_actions(): void
    {
        $indexView = file_get_contents(base_path('resources/views/nilai/index.blade.php'));
        $formativeView = file_get_contents(base_path('resources/views/nilai/asesmen_formatif.blade.php'));
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        $this->assertStringContainsString('@if($showBackNavigation)', $indexView);
        $this->assertStringContainsString('Kembali', $indexView);
        $this->assertStringContainsString('@unless($isSiswaWithoutClassPosition || $showBackNavigation)', $indexView);
        $this->assertStringContainsString("'source' => 'asesmen-formatif'", $formativeView);
        $this->assertStringContainsString("'edit' => 1", $formativeView);
        $this->assertStringContainsString('$showBackNavigation = $isEditMode || $isFormativeSource || $showInputForm || $isViewMode;', $controller);
    }

    public function test_formative_view_link_does_not_filter_to_one_session(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/asesmen_formatif.blade.php'));
        $viewLinkStart = strpos($view, "'view' => 1");
        $routeStart = strrpos(substr($view, 0, $viewLinkStart), "route('nilai.index'");
        $viewLink = substr($view, $routeStart, $viewLinkStart - $routeStart);

        $this->assertStringNotContainsString("'assessment_uuid'", $viewLink);
        $this->assertStringNotContainsString("'komponen_id'", $viewLink);
        $this->assertStringNotContainsString("'rencana_pembelajaran_id'", $viewLink);
    }

    public function test_add_score_column_posts_a_new_session_with_the_current_type(): void
    {
        $contents = file_get_contents(base_path('resources/views/nilai/index.blade.php'));
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        $this->assertStringContainsString('Tambah Kolom Nilai', $contents);
        $this->assertStringContainsString('name="jenis_penilaian" value="{{ optional($items->first())->komponen_id === null ? \'\' : optional($items->first())->nama_komponen }}"', $contents);
        $this->assertStringContainsString("'nama' => \$baseName . ' ' . \$assessmentCounts[\$baseName]", $controller);
        $this->assertStringNotContainsString("'assessment_uuid' => \$assessmentUuid,\n        ], function", $controller);
    }

    public function test_each_assessment_column_has_a_session_specific_delete_action(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        $this->assertStringContainsString('form="hapus-kolom-{{ md5($komponen->key) }}"', $view);
        $this->assertStringContainsString('name="assessment_uuid" value="{{ $komponen->assessment_uuid }}"', $view);
        $this->assertStringContainsString("\$query->where('assessment_uuid', \$validated['assessment_uuid']);", $controller);
    }

    public function test_grid_import_uses_the_new_template_and_session_importer(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));
        $importer = file_get_contents(base_path('app/Imports/NilaiInputGridImport.php'));

        $this->assertStringContainsString("route('nilai.import-input')", $view);
        $this->assertStringContainsString("route('nilai.template-input'", $view);
        $this->assertStringContainsString('name="assessment_uuid" value="{{ $selectedAssessmentUuid }}"', $view);
        $this->assertStringContainsString('new NilaiInputGridImport', $controller);
        $this->assertStringContainsString("where('assessment_uuid', \$session['assessment_uuid'])", $importer);
    }

    public function test_grid_import_template_contains_roster_and_assessment_column_mapping(): void
    {
        $export = new NilaiInputGridTemplateExport(
            [['id' => 10, 'nama' => 'AGUNG']],
            [
                ['column_code' => 'A1', 'type_code' => 1, 'type_name' => 'Harian / Umum', 'label' => 'Nilai Harian 1', 'scores' => [10 => 80]],
                ['column_code' => 'A2', 'type_code' => 1, 'type_name' => 'Harian / Umum', 'label' => 'Nilai Harian 2', 'scores' => [10 => null]],
            ],
            '12.A1',
            [
                ['type_code' => 1, 'type_name' => 'Harian / Umum'],
                ['type_code' => 2, 'type_name' => 'Asesmen awal/diagnostik'],
            ]
        );
        [$inputSheet, $guideSheet] = $export->sheets();

        $this->assertSame(['No', 'Nama', 'Jenis Penilaian', 'Kelas', 'A1', 'A2'], $inputSheet->headings());
        $this->assertSame([1, 'AGUNG', '1', '12.A1', 80, null], $inputSheet->array()[0]);
        $this->assertSame([1, 'Harian / Umum', '', ''], $guideSheet->array()[0]);
        $this->assertContains([1, 'Harian / Umum', 'A1', 'Nilai Harian 1'], $guideSheet->array());
        $this->assertContains([2, 'Asesmen awal/diagnostik', '', ''], $guideSheet->array());
    }

    public function test_grid_type_catalog_contains_all_standard_assessment_types(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));

        foreach ([
            'Harian / Umum',
            'Asesmen awal/diagnostik',
            'Observasi',
            'Tanya jawab',
            'Diskusi',
            'Kuis',
            'Latihan/tugas',
            'Praktik/unjuk kerja',
            'Penilaian diri',
            'Penilaian antarteman',
            'Refleksi',
            'Umpan balik',
            'Tindak lanjut (remedial/pengayaan)',
        ] as $typeName) {
            $this->assertStringContainsString("'{$typeName}'", $controller);
        }

        $this->assertStringContainsString("'type_code' => \$index + 1", $controller);
    }

    public function test_grid_import_preflight_detects_new_columns_and_rejects_mismatched_type(): void
    {
        $students = [['id' => 10, 'nama' => 'AGUNG']];
        $sessions = [[
            'assessment_uuid' => '123e4567-e89b-42d3-a456-426614174000',
            'column_code' => 'A1',
            'type_code' => 1,
        ]];
        $context = ['class_name' => '12.A1'];
        $rows = collect([[
            'no' => 1,
            'nama' => 'AGUNG',
            'jenis_penilaian' => '1',
            'kelas' => '12.A1',
            'a1' => 80,
            'a2' => 90,
        ]]);

        $preflight = new NilaiInputGridImport($students, $sessions, $context);
        $preflight->collection($rows);

        $this->assertSame([], $preflight->getErrors());
        $this->assertSame(['A1', 'A2'], $preflight->getColumnCodes());
        $this->assertSame(['A2'], $preflight->getNewColumnCodes());

        $mismatchedRows = collect([[
            'no' => 1,
            'nama' => 'AGUNG',
            'jenis_penilaian' => '2',
            'kelas' => '12.A1',
            'a1' => 80,
        ]]);
        $mismatched = new NilaiInputGridImport($students, $sessions, $context);
        $mismatched->collection($mismatchedRows);

        $this->assertNotEmpty($mismatched->getErrors());
    }

    public function test_grid_import_context_contains_fields_required_to_save_scores(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));
        $importContext = substr($controller, strpos($controller, 'new NilaiInputGridImport'), 900);

        $this->assertStringContainsString("'guru_id' => (int) \$guru->id", $importContext);
        $this->assertStringContainsString("'tahun_ajaran_id' => (int) \$tahunAjaranAktif->id", $importContext);
        $this->assertStringContainsString("'semester_id' => (int) \$semesterAktif->id", $importContext);
    }

    public function test_grid_import_requires_explicit_confirmation_before_overwriting(): void
    {
        $controller = file_get_contents(base_path('app/Http/Controllers/NilaiController.php'));
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));

        $this->assertStringNotContainsString("'confirm_overwrite' => 'nullable|accepted'", $controller);
        $this->assertStringContainsString('$import->getOverwriteCount()', $controller);
        $this->assertStringContainsString('$overwriteCount > 0 && ! $request->boolean(\'confirm_overwrite\')', $controller);
        $this->assertStringContainsString('name="confirm_overwrite" id="nilaiGridConfirmOverwrite"', $view);
        $this->assertStringContainsString('window.confirm(', $view);
    }

    public function test_input_actions_share_one_aligned_toolbar(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));

        $this->assertStringContainsString('d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2', $view);
        $this->assertStringContainsString('form="formSimpanNilai"', $view);
        $this->assertStringContainsString('id="formSimpanNilai"', $view);
    }

    public function test_grid_import_counts_only_changed_existing_scores_as_overwrites(): void
    {
        $session = [
            'assessment_uuid' => '123e4567-e89b-42d3-a456-426614174000',
            'column_code' => 'A1',
            'type_code' => 1,
            'scores' => [10 => 70],
        ];
        $import = new NilaiInputGridImport(
            [['id' => 10, 'nama' => 'AGUNG']],
            [$session],
            ['class_name' => '12.A1']
        );
        $import->collection(collect([[
            'no' => 1,
            'nama' => 'AGUNG',
            'jenis_penilaian' => '1',
            'kelas' => '12.A1',
            'a1' => 80,
        ]]));

        $this->assertSame(1, $import->getOverwriteCount());
    }

    public function test_active_grid_uses_the_new_import_and_template_routes(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString("route('nilai.import-input')", $view);
        $this->assertStringContainsString("route('nilai.preview-input')", $view);
        $this->assertStringContainsString("route('nilai.template-input'", $view);
        $this->assertStringContainsString('id="modalImportGridNilai"', $view);
        $this->assertStringContainsString("->name('nilai.template-input')", $routes);
        $this->assertStringContainsString("->name('nilai.preview-input')", $routes);
        $this->assertStringContainsString("->name('nilai.import-input')", $routes);
    }

    public function test_optional_tooltip_plugin_cannot_block_grid_import_script(): void
    {
        $view = file_get_contents(base_path('resources/views/nilai/index.blade.php'));

        $this->assertStringContainsString("typeof $.fn.tooltip === 'function'", $view);
        $this->assertStringContainsString('if (!selectEl || !items || !items.length) return;', $view);
        $this->assertStringContainsString("nilaiGridPreviewButton.addEventListener('click'", $view);
    }
}
