<?php
require_once __DIR__.'/init.php';

$gachaKey = 'hyakuren';
$contentFile = 'gacha_contents_hyakuren.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

$expct = $gacha->getGachaExpects('通常', '通常');

$itemList = $gacha->getItemList();

$buunExpct = [];
foreach ($expct as $key => $val) {
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
$fp = fopen(DATA_DIR.'/expect-hyakuren.tsv', 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);
echo "\n";

