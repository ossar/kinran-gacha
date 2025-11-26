<?php
require_once __DIR__.'/lib.php';



$expct = [];
$total = 0;
foreach ($gachaSetlist as $idx => $row) {
    $mode = $row['モード'];
    $res = getExpect($gachaDat, $gachaBreakdowns, $mode);
    foreach ($res as $name => $exp) {
        if (empty($expct[$name])) {
            $expct[$name] = 0;
        }
        $expct[$name] += $exp;
        $total += $exp;
    }
    echo "($idx) [$mode]\n";
    print_r($res);
    echo "\n";
}

$fp = fopen(__DIR__.'/out.tsv', 'w');
echo "[TOTAL] buun=$total\n";
foreach ($expct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);


function getExpect($gachaDat, $gachaBreakdowns, $mode) {
    $expct = [];
    $cate = $gachaDat[$mode]['category'];
    foreach ($gachaBreakdowns[$cate] as $row) {
        $prob = $row['確率'];
        foreach ($row['items'] as $itemType => $count) {
            if (!$count) {
                continue;
            }
            $res = getBuunExp($gachaDat, $mode, $itemType);
            if ($res) {
                #print_r($res);
            }
            foreach ($res as $name => $exp) {
                if (empty($expct[$name])) {
                    $expct[$name] = 0;
                }
                $expct[$name] += $exp * $count * $prob / 100;
            }
        }
    }
    return $expct;
}

function getBuunExp($gachaDat, $mode, $itemType) {
    static $memo;
    if (empty($gachaDat[$mode]['items'][$itemType])) {
        return [];
    }
    if (isset($memo[$mode][$itemType])) {
        return $memo[$mode][$itemType];
    }
    $memo[$mode][$itemType] = calcBuunExp($gachaDat[$mode]['items'][$itemType]);
    return $memo[$mode][$itemType];
}

function calcBuunExp($items) {
    $expcts = [];
    foreach ($items as $item) {
        switch ($item['種別']) {
        case '武将':
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
                var_dump($item);
                throw new RuntimeException('unkown rank');
                break;
            }
            $exp = $buun * $item['確率'] / 100 * $item['内容']['num'];
            if (empty($expcts[$item['内容']['name']])) {
                $expcts[$item['内容']['name']] = 0;
            }
            $expcts[$item['内容']['name']] += $exp;
            break;
        case '武運':
            $exp = $item['内容']['num'] * $item['確率'] / 100;
            if (empty($expcts[$item['内容']['name']])) {
                $expcts[$item['内容']['name']] = 0;
            }
            $expcts[$item['内容']['name']] += $exp;
            break;
        case '宝箱':
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
                var_dump($item);
                throw new RuntimeException('unkown rank');
                break;
            }
            $exp = $buun * $item['確率'] / 100 * $item['内容']['num'];
            $name = '選択';
            if (empty($expcts[$name])) {
                $expcts[$name] = 0;
            }
            $expcts[$name] += $exp;
            break;
        case 'アイテム':
            break;
        default:
            var_dump($item);
            throw new RuntimeException('Unkown item');
            break;
        }
    }
    return $expcts;
}

/*

foreach ($gachaDat as $mode => $row) {
    echo "[{$mode}]  ({$row['category']})\n";
    foreach ($row['items'] as $type => $items) {
        echo "[{$type}]\n";
        foreach ($items as $item) {
            #echo "\t - {$item['内容']} {$item['確率']}\n";
            switch ($item['内容']['type']) {
            case '武将':
                echo sprintf("%s\t%s (%s)\t%6.2f%%\n"
                    , $item['内容']['type']
                    , $item['内容']['name']
                    , $item['内容']['rank']
                    , $item['確率']
                );
                break;
            case '武運':
                echo sprintf("%s\t%s x%s\t%6.2f%%\n"
                    , $item['内容']['type']
                    , $item['内容']['name']
                    , $item['内容']['num']
                    , $item['確率']
                );
                break;
            default:
                echo sprintf("%s\t%s\t%6.2f%%\n"
                    , $item['内容']['type']
                    , $item['内容']['name']
                    , $item['確率']
                );
                break;
            }
        }
    }
    echo "\n";
}
 */


