<?php
require_once __DIR__.'/init.php';

$gachaKey = 'sr_pickup';
$contentFile = 'gacha_contents_sr_pickup.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

$expct = getTotalExpect($gacha);

$itemList = $gacha->getItemList();

$buunExpct = [];
foreach ($expct as $key => $exp) {
    if (!$res = $gacha->getBuun($itemList[$key])) {
        continue;
    }
    if (!$res[0] || !$res[1]) {
        continue;
    }
    list($name, $buun) = $res;
    if (!isset($buunExpct[$name])) {
        $buunExpct[$name] = 0;
    }
    $buunExpct[$name] += $exp * $buun ;
}

$file = DATA_DIR."/expct_{$gachaKey}.tsv";
$fp = fopen($file, "w");
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

echo $file,"\n";
