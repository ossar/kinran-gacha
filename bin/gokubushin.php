<?php
require_once __DIR__.'/init.php';

$gachaKey = 'gokubushin';
$contentFile = 'gacha_contents_gokubushin.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

$expct = $gacha->getGachaExpects('通常', '通常');
$itemList = $gacha->getItemList();
$buunNames = $gacha->getBuunNames($itemList);

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
    #echo "$key\t$exp\t$name\t$buun\n";
}
$file = DATA_DIR.'/expct_gokubushin.tsv';
$fp = fopen($file, "w");
foreach ([10, 20, 50, 100] as $num) {
    $line = "{$num}セットの武運期待値\n";
    fwrite($fp, $line);
    foreach ($buunExpct as $name => $buun) {
        $line = sprintf("%s\t%6.1f\n"
            , $name
            , $buun * $num
        );
        fwrite($fp, $line);
    }
    $line = "\n";
    fwrite($fp, $line);
}
fclose($fp);


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

