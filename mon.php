<?php
require_once __DIR__.'/lib.php';

// 集められる武運の一覧を取得
$buunKeys = getBuunKeys($gachaDat);


$repeatCount = 1000;

$fp = fopen(__DIR__.'/out.tsv', "w");
fwrite($fp, implode("\t", $buunKeys)."\n");
for ($i=0; $i<$repeatCount; $i++) {
    list($collects, $colBuun) = batchGacha($gachaSetlist, $gachaDat, $gachaBreakdowns);
    $cols = [];
    foreach ($buunKeys as $key) {
        $cols[] = $colBuun[$key]?? 0;
    }
    $line = implode("\t", $cols)."\n";
    fwrite($fp, $line);
    #foreach ($collects as $val) {
    #    echo sprintf("%d %s  %s  %s %s  \n"
    #        , $val['count']
    #        , $val['item']['内容']['type']
    #        , $val['item']['内容']['name']
    #        , $val['item']['内容']['rank']
    #        , $val['item']['内容']['num']
    #    );
    #}
    #print_r($colBuun);
}
fclose($fp);

/**
 * ガチャで出現する武運の武将一覧を取得する
 */
function getBuunKeys($gachaDat) {
    $buunKeys = [];
    foreach ($gachaDat as $mode => $row) {
        foreach ($row['items'] as $itemType => $items) {
            foreach ($items as $item) {
                switch ($itemType) {
                case '武運':
                    $name = $item['内容']['name'];
                    $buunKeys[$name] = $name;
                    break;
                case '武将':
                    $name = $item['内容']['name'];
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
function batchGacha($gachaSetlist, $gachaDat, $gachaBreakdowns) {
    $collects = [];
    $colBuun = [];
    foreach ($gachaSetlist as $idx => $row) {
        $mode = $row['モード'];
        $list = gacha($gachaDat, $gachaBreakdowns, $mode);
        foreach ($list as $item) {
            if ($res = getBuun($item)) {
                if (empty($colBuun[$res[0]])) {
                    $colBuun[$res[0]] = 0;
                }
                $colBuun[$res[0]] += $res[1];
            }
            $key = $item['内容']['itemKey'];
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
 * アイテムの武運換算数を取得する
 *
 */
function getBuun($item) {
    $buun = 0;
    $name = null;
    switch ($item['内容']['type']) {
    case '武運':
        $name = $item['内容']['name'];
        $buun = $item['内容']['num'];
        break;
    case '武将':
        $name = $item['内容']['name'];
        switch ($item['内容']['rank']) {
        case '星1':
            $buun = 15;
            break;
        case '星3':
            $buun = 44;
            break;
        case '星4':
            $buun = 140;
            break;
        case '星5':
            $buun = 340;
            break;
        case '星6':
            $buun = 620;
            break;
        default:
            throw new RuntimeException();
            break;
        }
        break;
    case '宝箱':
        $name = '選択宝箱';
        switch ($item['内容']['rank']) {
        case '星3':
            $buun = 44;
            break;
        case '星4':
            $buun = 140;
            break;
        case '星5':
            $buun = 340;
            break;
        case '星6':
            $buun = 620;
            break;
        default:
            throw new RuntimeException();
            break;
        }
        break;
    case 'アイテム':
        break;
    default:
        var_dump($item);
        throw new RuntimeException();
        break;
    }
    if (!$name || !$buun) {
        return false;
    }
    return [$name, $buun];
}

/**
 * モード指定されたガチャを1セット引く
 */
function gacha($gachaDat, $gachaBreakdowns, $mode) {
    $cate = $gachaDat[$mode]['category'];
    $set = getGachaSet($gachaBreakdowns[$cate]);
    $list = [];
    foreach ($set['items'] as $itemType => $count) {
        for ($i=0; $i<$count; $i++) {
            $list[] = getGacha($gachaDat[$mode]['items'][$itemType]);
        }
    }
    return $list;
}

/**
 * アイテム種別の出現割合を決定する
 * 出現割合セットの合計確率は100%になる必要がある
 */
function getGachaSet($gachaProb) {
    $max = 100000;
    $rand = random_int(0,$max)/$max*100;
    $get = null;
    $sum = 0;
    foreach ($gachaProb as $row) {
        $sum += $row['確率'];
        if ($sum >= $rand) {
            $get = $row;
            break;
        }
    }
    if (!$get) {
        $get = $row;
    }
    return $row;
}

/**
 * アイテムリストから一つゲットする
 * 各アイテムの出現確率の合計が100%になる必要がある
 */
function getGacha($items) {
    $max = 100000;
    $rand = random_int(0,$max)/$max*100;
    $sum = 0;
    $get = null;
    foreach ($items as $item) {
        $sum += $item['確率'];
        if ($sum>=$rand) {
            $get = $item;
            break;
        }
    }
    if (!$get) {
        $get = $item;
    }
    return $get;
}

