<?php
require 'vendor/autoload.php';

use App\Exports\KelasSiswaTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

// Generate file
$filename = 'test_template_' . time() . '.xlsx';
Excel::store(new KelasSiswaTemplateExport(1), $filename);

echo "File generated: $filename" . PHP_EOL;
echo "Location: " . getcwd() . "/$filename" . PHP_EOL;
