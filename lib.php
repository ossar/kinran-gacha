<?php



$gachaCategory = [
    "武将確定" => [
        ['確率'=>100, 'items' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]],
    ],
    "宝箱確定" => [
        ['確率'=>100, 'items' => ['武将'=> 0,'武運'=> 5, 'アイテム'=> 0, '宝箱'=> 1 ]],
    ],
    "通常" => [
        ['確率'=>10.5, 'items' => ['武将'=> 0,'武運'=> 2, 'アイテム'=> 4, '宝箱'=> 0 ]],
        ['確率'=>35.0, 'items' => ['武将'=> 0,'武運'=> 3, 'アイテム'=> 3, '宝箱'=> 0 ]],
        ['確率'=>24.5, 'items' => ['武将'=> 0,'武運'=> 4, 'アイテム'=> 2, '宝箱'=> 0 ]],
        ['確率'=> 4.5, 'items' => ['武将'=> 1,'武運'=> 2, 'アイテム'=> 3, '宝箱'=> 0 ]],
        ['確率'=>15.0, 'items' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]],
        ['確率'=>10.5, 'items' => ['武将'=> 1,'武運'=> 4, 'アイテム'=> 1, '宝箱'=> 0 ]],
    ],
];


$gachaSet = [
    1  => ['カテゴリ' => '通常'    , 'モード'=>'通常'            ],
    2  => ['カテゴリ' => '通常'    , 'モード'=>'通常'            ],
    3  => ['カテゴリ' => '武将確定', 'モード'=>'武将星3〜4確定'  ],
    4  => ['カテゴリ' => '通常'    , 'モード'=>'通常'            ],
    5  => ['カテゴリ' => '宝箱確定', 'モード'=>'宝箱星3〜4箱確定'],
    6  => ['カテゴリ' => '通常'    , 'モード'=>'通常'            ],
    7  => ['カテゴリ' => '武将確定', 'モード'=>'武将星4確定'     ],
    8  => ['カテゴリ' => '通常'    , 'モード'=>'通常'            ],
    9  => ['カテゴリ' => '宝箱確定', 'モード'=>'宝箱星4箱確定'   ],
    10 => ['カテゴリ' => '宝箱確定', 'モード'=>'宝箱星5箱確定'   ],
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
        if (empty($dat[$buf['モード']])) {
            $dat[$buf['モード']] = [
                'category' => $buf['カテゴリ'],
                'items' => [],
            ];
        }
        $dat[$buf['モード']]['items'][$buf['種別']][] = [
            '種別' => $buf['種別'],
            '内容' => parseContent($buf['内容'], $buf['種別']),
            '確率' => parseProb($buf['確率']),
        ];

    }
    fclose($fp);
    return $dat;
}

