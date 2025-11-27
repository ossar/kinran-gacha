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
        $res = getGachaExpct($gachaDat[$gachaMode], $gachaTypeSlots[$gachaType]);
        $memo[$gachaMode] = $res;
    }
    foreach ($res as $key => $val) {
        if (!isset($expct[$key])) {
            $expct[$key] = 0;
        }
        $expct[$key] += $val;
    }
    continue;
}

$total = 0;
$buunExpct = [];
foreach ($expct as $key => $val) {
    list($itemType, $itemStr) = explode(":", $key);
    $item = parseContent($itemStr, $itemType);
    if (!$res = getBuun($item)) {
        continue;
    }
    list($name, $buun) = $res;
    if (!$name || !$buun) {
        continue;
    }
    if (!isset($buunExpct[$name])) {
        $buunExpct[$name] = 0;
    }
    $exp = $buun * $val;
    $buunExpct[$name] += $exp;
    $total += $exp;
}

print_r($expct);
print_r($buunExpct);

$fp = fopen(__DIR__.'/expout.tsv', 'w');
echo "[TOTAL] buun=$total\n";
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

function getGachaExpct($gachaContents, $gachaSlots) {
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

