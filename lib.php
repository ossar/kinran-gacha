<?php

$datFile = __DIR__.'/gacha_contents.tsv';

$gachaDat = loadDatFile($datFile);

$gachaSets = [
    1  => [ 'gachaMode'=>'通常'            , 'gachaType' => '通常'     ],
    2  => [ 'gachaMode'=>'通常'            , 'gachaType' => '通常'     ],
    3  => [ 'gachaMode'=>'武将星3〜4確定'  , 'gachaType' => '武将確定' ],
    4  => [ 'gachaMode'=>'通常'            , 'gachaType' => '通常'     ],
    5  => [ 'gachaMode'=>'宝箱星3〜4箱確定', 'gachaType' => '宝箱確定' ],
    6  => [ 'gachaMode'=>'通常'            , 'gachaType' => '通常'     ],
    7  => [ 'gachaMode'=>'武将星4確定'     , 'gachaType' => '武将確定' ],
    8  => [ 'gachaMode'=>'通常'            , 'gachaType' => '通常'     ],
    9  => [ 'gachaMode'=>'宝箱星4箱確定'   , 'gachaType' => '宝箱確定' ],
    10 => [ 'gachaMode'=>'宝箱星5箱確定'   , 'gachaType' => '宝箱確定' ],
];

$gachaTypeSlots = [
    "武将確定" => [ ['確率'=>100,  'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]]],
    "宝箱確定" => [ ['確率'=>100,  'slots' => ['武将'=> 0,'武運'=> 5, 'アイテム'=> 0, '宝箱'=> 1 ]]],
    "通常"     => [ ['確率'=>10.5, 'slots' => ['武将'=> 0,'武運'=> 2, 'アイテム'=> 4, '宝箱'=> 0 ]],
                    ['確率'=>35.0, 'slots' => ['武将'=> 0,'武運'=> 3, 'アイテム'=> 3, '宝箱'=> 0 ]],
                    ['確率'=>24.5, 'slots' => ['武将'=> 0,'武運'=> 4, 'アイテム'=> 2, '宝箱'=> 0 ]],
                    ['確率'=> 4.5, 'slots' => ['武将'=> 1,'武運'=> 2, 'アイテム'=> 3, '宝箱'=> 0 ]],
                    ['確率'=>15.0, 'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]],
                    ['確率'=>10.5, 'slots' => ['武将'=> 1,'武運'=> 4, 'アイテム'=> 1, '宝箱'=> 0 ]]],
];

function parseProb(string $prob) {
    $res = rtrim($prob, "%");
    return floatval($res);
}

function parseContent(string $itemStr, string $type) {
    $dat = [
        'itemKey' => "{$type}:{$itemStr}",
        'type' => $type,
        'name' => null,
        'num'  => null,
        'rank' => null,
    ];
    switch ($type) {
    case '武将':
        $buf = explode(" ", $itemStr);
        $dat['name'] = trim($buf[0]);
        $dat['rank'] = trim($buf[1]);
        $dat['num']  = 1;
        break;
    case '武運':
        $buf = explode('x', $itemStr);
        $dat['name'] = trim($buf[0]);
        $dat['num'] = intval(trim($buf[1]));
        break;
    case '宝箱':
        $dat['name'] = $itemStr;
        $dat['num']  = 1;
        if (preg_match('/(星\d)/u', $itemStr, $match)) {
            $dat['rank'] = $match[1];
        }
        break;
    case 'アイテム':
        $dat['name'] = $itemStr;
        $dat['num']  = 1;
        break;
    default:
        var_dump($type);
        var_dump($itemStr);
        throw new RuntimeException('Unknown item type.');
        break;
    }
    return $dat;
}

function loadDatFile($filename) {
    $dat = [];
    $keys = [];
    $fp = fopen($filename, "r");
    while (FALSE !== $line=fgets($fp)) {
        $line = rtrim($line, "\r\n");
        $cols = explode("\t", $line);
        if (!$keys) {
            $keys = $cols;
            continue;
        }
        $buf = [];
        foreach ($cols as $idx => $val) {
            $buf[$keys[$idx]] = $val;
        }
        if (empty($dat[$buf['ガチャモード']])) {
            $dat[$buf['ガチャモード']] = [];
        }
        $dat[$buf['ガチャモード']][$buf['排出タイプ']][] = [
            '排出タイプ' => $buf['排出タイプ'],
            '排出内容' => parseContent($buf['排出内容'], $buf['排出タイプ']),
            '確率' => parseProb($buf['確率']),
        ];
    }
    fclose($fp);
    return $dat;
}

/**
 * アイテムの武運換算数を取得する
 *
 */
function getBuun(array $item):array|bool {
    $name = '';
    $buun = 0;
    switch ($item['type']) {
    case '武将':
        $name = $item['name'];
        switch ($item['rank']) {
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
    case '武運':
        $name = $item['name'];
        $buun = $item['num'];
        break;
    case '宝箱':
        $name = '選択宝箱';
        switch ($item['rank']) {
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
 * くじを引く
 * 合計が100になる配列を受け取り、確率にそってindexを返す
 */
function getProbItems(array $probs):int|bool {
    if (!$probs) {
        return false;
    }
    $maxNum = 1000000;
    $rand = random_int(0, $maxNum)/$maxNum * 100;
    $sum = 0;
    $idx = 0;
    foreach ($probs as $prob) {
        $sum += $prob;
        if ($sum >= $rand) {
            break;
        }
        if ($idx >= sizeof($probs)-1) {
            break;
        }
        $idx++;
    }
    return $idx;
}

