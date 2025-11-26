<?php
require_once __DIR__.'/lib.php';

$gachaDat = loadDatFile(__DIR__.'/gacha_contents.tsv');

#print_r($gachaDat);

foreach ($gachaDat as $mode => $val) {
    #echo "[$mode] ({$val['category']})\n";
    foreach ($val['items'] as $itemType => $items) {
        #echo "#$itemType\n";
        $res = getGacha($items);
        #echo implode("/", $res['内容'])."\n";
    }
}
$buunKeys = buunKeys($gachaDat);

$repeatCount = 1500;

$fp = fopen(__DIR__.'/out.tsv', "w");
fwrite($fp, implode("\t", $buunKeys)."\n");
for ($i=0; $i<$repeatCount; $i++) {
    list($collects, $colBuun) = batchGacha($gachaSet, $gachaDat, $gachaCategory);
    $cols = [];
    foreach ($buunKeys as $key) {
        $cols[] = $colBuun[$key]?? 0;
    }
    $line = implode("\t", $cols)."\n";
    fwrite($fp, $line);
    /*
    echo "[$i]\n";
    foreach ($colBuun as $name => $count) {
        echo "$name\t\t$count\n";
    }
    echo "\n";
     */
}
fclose($fp);



function buunKeys($gachaDat) {
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


function batchGacha($gachaSet, $gachaDat, $gachaCategory) {
    $collects = [];
    $colBuun = [];
    foreach ($gachaSet as $idx => $row) {
        $mode = $row['モード'];
        $list = gacha($gachaDat, $gachaCategory, $mode);
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


function dumpList($list) {
    foreach ($list as $item) {
        echo sprintf(
            "%s  %s  %s  (%s)  {%s}\n"
            , $item['内容']['type']
            , $item['内容']['name']
            , $item['内容']['rank']
            , $item['内容']['num']
            , $item['内容']['itemKey']
        );
    }
}

function gacha($gachaDat, $gachaCategory, $mode) {
    $cate = $gachaDat[$mode]['category'];
    $set = getGachaSet($gachaCategory[$cate]);
    $list = [];
    foreach ($set['items'] as $itemType => $count) {
        for ($i=0; $i<$count; $i++) {
            $list[] = getGacha($gachaDat[$mode]['items'][$itemType]);
        }
    }
    return $list;
}
function getGachaSet($gachaSet) {
    $max = 100000;
    $rand = random_int(0,$max)/$max*100;
    $get = null;
    $sum = 0;
    foreach ($gachaSet as $row) {
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
        continue;

        echo sprintf("  %8.2f\t(%s)\t%s\trank=%s\tnum=%s\n"
            , $item['確率']
            , $item['内容']['type']
            , $item['内容']['name']
            , $item['内容']['rank']
            , $item['内容']['num']
        );
    }
    if (!$get) {
        $get = $item;
    }
    return $get;
    var_dump($rand);
    print_r($item);
    echo "合計={$sum}\n\n";
}

