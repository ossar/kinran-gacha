<?php
namespace MyApp;

require_once __DIR__.'/init.php';

$gachaKey = 'pickup6';
$contentFile = 'gacha_contents_pickup6.tsv';
$gacha = getGacha($gachaKey, $contentFile);

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
$setNum = 100;
$outFile = sprintf("out-%s-%03d.tsv", $gachaKey, $setNum);
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
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

echo "\n{$outFile}\n";
