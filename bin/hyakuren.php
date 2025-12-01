<?php
require_once __DIR__.'/include_hyakuren.php';

$gachaMode = '通常';
$gachaType = '通常';

$expct = $gacha->getGachaExpects($gachaMode, $gachaType);

print_r($expct);


$total = 0;
$buunExpct = [];
foreach ($expct as $key => $val) {
    list($itemType, $itemStr) = explode(':', $key);
    $item = $gacha->parseContent($itemStr, $itemType);
    if (!$res = $gacha->getBuun($item)) {
        continue;
    }
    if (!$res[0] || !$res[1]) {
        continue;
    }
    list($name, $buun) = $res;
    if (!isset($buunExpct[$name])) {
        $buunExpct[$name] = 0;
    }
    $exp = $buun * $val;
    $buunExpct[$name] += $exp;
    $total += $exp;
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
$fp = fopen(dirname(__DIR__).'/dat/expout.tsv', 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);
echo "\n";
echo "[武運合計] buun=$total\n";

print_r($res);
