<?php

require __DIR__.'/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$templatePath = __DIR__ . '/template/jadwal_kelas.xlsx';

if (!file_exists($templatePath)) {
    die("Template file tidak ditemukan!\n");
}

$spreadsheet = IOFactory::load($templatePath);
$sheet = $spreadsheet->getActiveSheet();

echo "=== TEMPLATE JADWAL KELAS ===\n\n";
echo "Sheet Name: " . $sheet->getTitle() . "\n";
echo "Highest Row: " . $sheet->getHighestRow() . "\n";
echo "Highest Column: " . $sheet->getHighestColumn() . "\n\n";

echo "=== ISI TEMPLATE ===\n\n";

$highestRow = $sheet->getHighestRow();
$highestColumn = $sheet->getHighestColumn();

for ($row = 1; $row <= min($highestRow, 30); $row++) {
    echo "Row $row: ";
    for ($col = 'A'; $col <= min($highestColumn, 'M'); $col++) {
        $value = $sheet->getCell($col . $row)->getValue();
        if (!empty($value)) {
            echo "[$col: $value] ";
        }
    }
    echo "\n";
}

// Check merged cells
echo "\n=== MERGED CELLS ===\n";
$mergeCells = $sheet->getMergeCells();
foreach ($mergeCells as $mergeCell) {
    echo "$mergeCell\n";
}

// Check styles (borders, colors, etc)
echo "\n=== CELL STYLES (First 10 rows) ===\n";
for ($row = 1; $row <= min($highestRow, 10); $row++) {
    for ($col = 'A'; $col <= min($highestColumn, 'M'); $col++) {
        $cell = $sheet->getCell($col . $row);
        $style = $cell->getStyle();
        
        if ($style->getFont()->getBold() || $style->getFill()->getFillType() !== \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE) {
            echo "Cell $col$row: ";
            if ($style->getFont()->getBold()) {
                echo "BOLD ";
            }
            if ($style->getFill()->getFillType() !== \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE) {
                echo "FILLED ";
            }
            echo "\n";
        }
    }
}
