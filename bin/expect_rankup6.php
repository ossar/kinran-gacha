<?php
namespace MyApp;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup6';
$contentFile = 'gacha_contents_rankup6.tsv';
$gacha = getGacha($gachaKey, $contentFile);

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
$outFile = "expect-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
