<?php
namespace MyApp;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup5';
$contentFile = 'gacha_contents_rankup5.tsv';
$gacha = getGacha($gachaKey, $contentFile);

// 集められる武運の一覧を取得
$buunKeys = $gacha->getBuunKeys();

$outFile = "out-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
$line = implode("\t", $buunKeys)."\n";
fwrite($fp, $line);
echo $line;

$repeatCount = 1000;
for ($i=0; $i<$repeatCount; $i++) {
    list($collects, $colBuun) = $gacha->batchGacha();
    $cols = [];
    foreach ($buunKeys as $key) {
        $cols[] = $colBuun[$key]?? 0;
    }
    $line = implode("\t", $cols)."\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
