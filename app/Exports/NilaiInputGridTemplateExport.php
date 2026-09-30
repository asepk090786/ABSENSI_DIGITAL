<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class NilaiInputGridTemplateExport implements WithMultipleSheets
{
    public function __construct(
        private array $students,
        private array $sessions,
        private string $className,
        private array $typeCatalog = []
    ) {
    }

    public function sheets(): array
    {
        return [
            new NilaiInputGridMainSheet($this->students, $this->sessions, $this->className),
            new NilaiInputGridGuideSheet($this->sessions, $this->typeCatalog),
        ];
    }
}

class NilaiInputGridMainSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(
        private array $students,
        private array $sessions,
        private string $className
    ) {
    }

    public function headings(): array
    {
        return array_merge(
            ['No', 'Nama', 'Jenis Penilaian', 'Kelas'],
            array_column($this->sessions, 'column_code')
        );
    }

    public function array(): array
    {
        $typeCodes = array_values(array_unique(array_column($this->sessions, 'type_code')));
        $typeCodeValue = implode(';', $typeCodes);

        return array_map(function ($student, $index) use ($typeCodeValue) {
            $scores = array_map(function ($session) use ($student) {
                return $session['scores'][$student['id']] ?? null;
            }, $this->sessions);

            return array_merge(
                [$index + 1, $student['nama'], $typeCodeValue, $this->className],
                $scores
            );
        }, $this->students, array_keys($this->students));
    }

    public function title(): string
    {
        return 'Input Nilai';
    }
}

class NilaiInputGridGuideSheet implements FromArray, WithHeadings, WithTitle, ShouldAutoSize
{
    public function __construct(private array $sessions, private array $typeCatalog)
    {
    }

    public function headings(): array
    {
        return ['Kode Jenis', 'Jenis Penilaian', 'Kode Kolom', 'Keterangan Kolom'];
    }

    public function array(): array
    {
        $typeRows = array_map(function ($type) {
            return [$type['type_code'], $type['type_name'], '', ''];
        }, $this->typeCatalog);
        $sessionRows = array_map(function ($session) {
            return [
                $session['type_code'],
                $session['type_name'],
                $session['column_code'],
                $session['label'],
            ];
        }, $this->sessions);

        return array_merge($typeRows, $sessionRows);
    }

    public function title(): string
    {
        return 'Keterangan';
    }
}