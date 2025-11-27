<?php
require_once __DIR__.'/lib.php';

// 集められる武運の一覧を取得
$buunKeys = getBuunKeys($gachaModeContents);

$fp = fopen(__DIR__.'/out.tsv', "w");
$line = implode("\t", $buunKeys)."\n";
fwrite($fp, $line);
echo $line;

$repeatCount = 1000;
for ($i=0; $i<$repeatCount; $i++) {
    list($collects, $colBuun) = batchGacha($gachaSets, $gachaModeContents, $gachaTypeSlots);
    $cols = [];
    foreach ($buunKeys as $key) {
        $cols[] = $colBuun[$key]?? 0;
    }
    $line = implode("\t", $cols)."\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

/**
 * ガチャで出現する武運の武将一覧を取得する
 */
function getBuunKeys($gachaModeContents) {
    $buunKeys = [];
    foreach ($gachaModeContents as $mode => $row) {
        foreach ($row as $itemType => $items) {
            foreach ($items as $item) {
                switch ($itemType) {
                case '武運':
                    $name = $item['排出内容']['name'];
                    $buunKeys[$name] = $name;
                    break;
                case '武将':
                    $name = $item['排出内容']['name'];
                    $buunKeys[$name] = $name;
                    break;
                case '宝箱':
                    $name = '選択宝箱';
                    $buunKeys[$name] = $name;
                    break;
                default:
                    break;
                }
            }
        }
    }
    return array_values($buunKeys);
}


/**
 * ガチャを最後まで引き切る
 *
 */
function batchGacha($gachaSets, $gachaModeContents, $gachaTypeSlots) {
    $collects = [];
    $colBuun = [];
    foreach ($gachaSets as $idx => $row) {
        $gachaMode = $row['gachaMode'];
        $gachaType = $row['gachaType'];
        $list = gacha($gachaModeContents[$gachaMode], $gachaTypeSlots[$gachaType]);
        foreach ($list as $row) {
            $item = $row['排出内容'];
            if ($res = getBuun($item)) {
                if ($res[0] && $res[1]) {
                    if (empty($colBuun[$res[0]])) {
                        $colBuun[$res[0]] = 0;
                    }
                    $colBuun[$res[0]] += $res[1];
                }
            }
            $key = $item['itemKey'];
            if (empty($collects[$key])) {
                $collects[$key] = [
                    'count' => 0,
                    'item'  => $item,
                ];
            }
            $collects[$key]['count']++;
        }
    }
    return [$collects, $colBuun];
}

/**
 * モード指定されたガチャを1セット引く
 */
function gacha($gachaContents, $gachaSlots) {
    $list = [];
    // スロットの決定
    $probs  = array_column($gachaSlots, '確率');
    $values = array_values($gachaSlots);
    $idx = getProbItems($probs);
    $slot = $values[$idx];
    foreach ($slot['slots'] as $itemType => $count) {
        for ($i=0; $i<$count; $i++) {
            // アイテムの決定
            $probs  = array_column($gachaContents[$itemType], '確率');
            $values = array_values($gachaContents[$itemType]);
            $idx = getProbItems($probs);
            $list[] = $values[$idx];
        }
    }
    return $list;
}

