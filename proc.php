<?php
require_once __DIR__.'/include.php';

$expct = [];
$memo = [];
foreach ($gachaSets as $idx => $row) {
    $gachaMode = $row['gachaMode'];
    $gachaType = $row['gachaType'];
    #echo "[{$idx}] {$gachaMode} ({$gachaType})\n";;
    if (isset($memo[$gachaMode])) {
        $res = $memo[$gachaMode];
    } else {
        $res = $gacha->getGachaExpct($gachaModeContents[$gachaMode], $gachaTypeSlots[$gachaType]);
        $memo[$gachaMode] = $res;
    }
    foreach ($res as $key => $val) {
        if (!isset($expct[$key])) {
            $expct[$key] = 0;
        }
        $expct[$key] += $val;
    }
}

$total = 0;
$buunExpct = [];
foreach ($expct as $key => $val) {
    list($itemType, $itemStr) = explode(":", $key);
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
$fp = fopen(__DIR__.'/expout.tsv', 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);
echo "\n";
echo "[武運合計] buun=$total\n";

