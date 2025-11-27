<?php
require_once __DIR__.'/lib.php';

$expct = [];
$memo = [];
foreach ($gachaSets as $idx => $row) {
    $gachaMode = $row['gachaMode'];
    $gachaType = $row['gachaType'];
    #echo "[{$idx}] {$gachaMode} ({$gachaType})\n";;
    if (isset($memo[$gachaMode])) {
        $res = $memo[$gachaMode];
    } else {
        $res = getGachaExpct($gachaModeContents[$gachaMode], $gachaTypeSlots[$gachaType]);
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
    $item = parseContent($itemStr, $itemType);
    if (!$res = getBuun($item)) {
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

/**
 * ガチャ1セットの期待値を出す
 *
 * @param   array   排出内容
 * @param   array   スロット一覧
 * @return  array   [アイテムのキー=>期待値] の配列
 */
function getGachaExpct(array $gachaContents, array $gachaSlots):array {
    $expct = [];
    foreach ($gachaSlots as $slot) {
        $slotProb = $slot['確率'];
        foreach ($slot['slots'] as $itemType => $slotCount) {
            if (!$slotCount) {
                continue;
            }
            foreach ($gachaContents[$itemType] as $row) {
                $itemProb = $row['確率'];
                $itemKey = $row['排出内容']['itemKey'];
                $exp = $slotProb / 100 * $itemProb / 100 * $slotCount * 1;
                if (empty($expct[$itemKey])) {
                    $expct[$itemKey] = 0;
                }
                $expct[$itemKey] += $exp;
            }
        }
    }
    return $expct;
}

