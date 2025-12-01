<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
$gachaConfig = include dirname(__DIR__).'/config/gacha_config.php';

$gachaContentsFile = dirname(__DIR__).'/config/gacha_contents_gokubushin.tsv';

$config = $gachaConfig[2];

echo $config['gacha_name'],"\n";

$gacha = new \MyApp\Gacha();
$gacha->gachaTypeSlots = $config['gacha_type_slots'];
$gacha->gachaSets = $config['gacha_sets'];
$gacha->loadGachaData($gachaContentsFile);

#print_r($gacha->getItemList());

$expct = $gacha->getGachaExpects('通常', '通常');
#print_r($expct);

$itemList = $gacha->getItemList();
$buunNames = $gacha->getBuunNames($itemList);
#print_r($buunNames);


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
$file = dirname(__DIR__).'/dat/expct_gokubushin.tsv';
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
$outFile = dirname(__DIR__)."/dat/gokubushin-{$setNum}.tsv";
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

