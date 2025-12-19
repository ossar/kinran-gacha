<?php
namespace MyApp;

require_once __DIR__.'/init.php';

$gachaKey = 'hyakuren';
$contentFile = 'gacha_contents_hyakuren2.tsv';
$gacha = getGacha($gachaKey, $contentFile);

$expct = $gacha->getGachaExpects('通常', '通常');

$itemList = $gacha->getItemList();

if (false) {

$buunExpct = [];
foreach ($expct as $key => $val) {
    if (!$res = $itemList[$key]->getItemBuun()) {
        continue;
    }
    list($name, $buun) = $res;
    if (!isset($buunExpct[$name])) {
        $buunExpct[$name] = 0;
    }
    $buunExpct[$name] += $buun * $val;
}

echo "=========アイテムの期待値========\n";
foreach ($expct as $key => $val) {
    echo sprintf("%s\t%s\n"
        , $key
        , $val
    );
}
echo "\n";

echo "=========武運期待値========\n";
$outFile = "expect-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";

exit;
}

// 集められる武運の一覧を取得
$buunKeys = $gacha->getBuunKeys();

$outFile = "out-{$gachaKey}2.tsv";
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
