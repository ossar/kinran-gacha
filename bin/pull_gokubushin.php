<?php
require_once __DIR__.'/init.php';

$gachaKey = 'gokubushin';
$contentFile = 'gacha_contents_gokubushin.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

$itemList = $gacha->getItemList();
$buunNames = $gacha->getBuunNames($itemList);

$pull = function($num) use ($gacha, $buunNames ) {
    $colBuun = [];
    foreach ($buunNames as $name) {
        $colBuun[$name] = 0;
    }
    for ($i=0; $i<$num; $i++) {
        list($col, $bun) = $gacha->batchGacha();
        foreach ($bun as $key => $val) {
            $colBuun[$key] += $val;
        }
    }
    return $colBuun;
};

$count = 1000;
$setNum = 10;
$outFile = DATA_DIR."/gokubushin-{$setNum}.tsv";
$fp = fopen($outFile, "w");
for ($i=0; $i<$count; $i++) {
    $res = $pull($setNum);
    if ($i==0) {
        $line = implode("\t", array_keys($res))."\n";
        echo $line;
        fwrite($fp, $line);
    }
    $line = implode("\t", $res)."\n";
    echo $line;
    fwrite($fp, $line);
}
fclose($fp);

