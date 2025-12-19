<?php
namespace MyApp;

require_once __DIR__.'/init.php';

$gachaKey = 'pickup6';
$contentFile = 'gacha_contents_pickup6.tsv';
$gacha = getGacha($gachaKey, $contentFile);

$expct = getTotalExpect($gacha);

$itemList = $gacha->getItemList();

$buunExpct = [];
foreach ($expct as $key => $exp) {
    if (!$res = $itemList[$key]->getItemBuun()) {
        continue;
    }
    list($name, $buun) = $res;
    if (!isset($buunExpct[$name])) {
        $buunExpct[$name] = 0;
    }
    $buunExpct[$name] += $exp * $buun ;
}

$outFile = "expct_{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
foreach ([10, 20, 50, 100] as $num) {
    $line = "{$num}セットの武運期待値\n";
    fwrite($fp, $line);
    echo $line;
    foreach ($buunExpct as $name => $buun) {
        $line = sprintf("%s\t%6.1f\n"
            , $name
            , $buun * $num
        );
        fwrite($fp, $line);
        echo $line;
    }
    $line = "\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
