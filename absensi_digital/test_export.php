<?php
require 'vendor/autoload.php';

$export = new \App\Exports\KelasSiswaTemplateExport(1);
$data = $export->array();

echo "Total baris: " . count($data) . PHP_EOL;
for ($i = 0; $i < count($data); $i++) {
    echo "Baris " . ($i + 1) . ": ";
    if (is_array($data[$i])) {
        echo implode(' | ', $data[$i]);
    } else {
        echo "KOSONG";
    }
    echo PHP_EOL;
}
