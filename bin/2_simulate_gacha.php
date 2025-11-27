<?php
require_once __DIR__.'/include.php';

// 集められる武運の一覧を取得
$buunKeys = $gacha->getBuunKeys($gachaModeContents);

$fp = fopen(dirname(__DIR__).'/dat/out.tsv', "w");
$line = implode("\t", $buunKeys)."\n";
fwrite($fp, $line);
echo $line;

$repeatCount = 1000;
for ($i=0; $i<$repeatCount; $i++) {
    list($collects, $colBuun) = $gacha->batchGacha($gachaSets, $gachaModeContents, $gachaTypeSlots);
    $cols = [];
    foreach ($buunKeys as $key) {
        $cols[] = $colBuun[$key]?? 0;
    }
    $line = implode("\t", $cols)."\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

