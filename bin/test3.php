<?php
require_once __DIR__.'/init.php';

$gachaKey = 'rankup5';
$contentFile = 'gacha_contents_rankup5.tsv';
$gacha = getGacha($gachaKey, $contentFile);

/*
$expct = getTotalExpect($gacha);

$itemList = $gacha->getItemList();

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
$fp = fopen(DATA_DIR.'/expect-rankup5.tsv', 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);
echo "\n";

exit;
 */

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
