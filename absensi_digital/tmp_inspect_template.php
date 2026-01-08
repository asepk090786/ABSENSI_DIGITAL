<?php
$path = __DIR__ . '/template/import_akun_pengguna.xlsx';
$zip = new ZipArchive();
if ($zip->open($path) !== true) {
    fwrite(STDERR, "fail\n");
    exit(1);
}
$sheet = $zip->getFromName('xl/worksheets/sheet1.xml');
$sharedXml = $zip->getFromName('xl/sharedStrings.xml');
$shared = [];
if ($sharedXml) {
    $sx = simplexml_load_string($sharedXml);
    foreach ($sx->si as $si) {
        $shared[] = (string) $si->t;
    }
}
$ns = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
$sheetXml = simplexml_load_string($sheet);
$sheetXml->registerXPathNamespace('a', $ns);
$rows = $sheetXml->xpath('//a:sheetData/a:row');
$out = [];
foreach (array_slice($rows, 0, 15) as $row) {
    $line = [];
    foreach ($row->c as $c) {
        $v = $c->v;
        $t = (string) $c['t'];
        if (!$v) {
            $line[] = '';
            continue;
        }
        if ($t === 's') {
            $idx = (int) $v;
            $line[] = $shared[$idx] ?? (string) $v;
        } else {
            $line[] = (string) $v;
        }
    }
    $out[] = $line;
}
print_r($out);
