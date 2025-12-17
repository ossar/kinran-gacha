<?php

return [
    [
        'gacha_key' => 'rankup5',
        'gacha_name' => '星5ランクアップガチャ',
        'gacha_sets'  => [
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
        ],
        'gacha_type_slots' => [
            "武将確定" => [
                ['確率'=>100,  'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]]
            ],
            "宝箱確定" => [
                ['確率'=>100,  'slots' => ['武将'=> 0,'武運'=> 5, 'アイテム'=> 0, '宝箱'=> 1 ]]
            ],
            "通常"     => [
                ['確率'=>10.5, 'slots' => ['武将'=> 0,'武運'=> 2, 'アイテム'=> 4, '宝箱'=> 0 ]],
                ['確率'=>35.0, 'slots' => ['武将'=> 0,'武運'=> 3, 'アイテム'=> 3, '宝箱'=> 0 ]],
                ['確率'=>24.5, 'slots' => ['武将'=> 0,'武運'=> 4, 'アイテム'=> 2, '宝箱'=> 0 ]],
                ['確率'=> 4.5, 'slots' => ['武将'=> 1,'武運'=> 2, 'アイテム'=> 3, '宝箱'=> 0 ]],
                ['確率'=>15.0, 'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]],
                ['確率'=>10.5, 'slots' => ['武将'=> 1,'武運'=> 4, 'アイテム'=> 1, '宝箱'=> 0 ]],
            ],
        ],
    ],
    [
        'gacha_key' => 'hyakuren',
        'gacha_name' => '百連祭',
        'gacha_sets' => [
            1 => ['gachaMode' => '通常', 'gachaType' => '通常'],
            2 => ['gachaMode' => '通常', 'gachaType' => '通常'],
            3 => ['gachaMode' => '通常', 'gachaType' => '通常'],
            4 => ['gachaMode' => '通常', 'gachaType' => '通常'],
            5 => ['gachaMode' => '通常', 'gachaType' => '通常'],
        ],
        'gacha_type_slots' => [
            "通常" => [
                [ "確率" => 100, "slots" => [ "*" => 100 ]],
            ],
        ],
    ],
    [
        'gacha_key' => 'gokubushin',
        'gacha_name' => '極武神',
        'gacha_sets' => [
            1 => ['gachaMode' => '通常', 'gachaType' => '通常'],
        ],
        'gacha_type_slots' => [
            "通常" => [
                [ "確率" => 100, "slots" => [ "*" => 6 ]],
            ],
        ],
    ],
    [
        'gacha_key' => 'rankup6',
        'gacha_name' => '星6ランクアップガチャ',
        'gacha_sets'  => [
            1  => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            2  => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            3  => [ 'gachaMode'=>'武将星3〜4確定' , 'gachaType' => '武将確定' ],
            4  => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            5  => [ 'gachaMode'=>'武将星3〜4確定' , 'gachaType' => '武将確定' ],
            6  => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            7  => [ 'gachaMode'=>'武将星3〜4確定' , 'gachaType' => '武将確定' ],
            8  => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            9  => [ 'gachaMode'=>'武将星4確定'    , 'gachaType' => '武将確定' ],
            10 => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            11 => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            12 => [ 'gachaMode'=>'武将星5確定'    , 'gachaType' => '武将確定' ],
            13 => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            14 => [ 'gachaMode'=>'通常'           , 'gachaType' => '通常'     ],
            15 => [ 'gachaMode'=>'宝箱星6箱確定'  , 'gachaType' => '宝箱確定' ],
        ],
        'gacha_type_slots' => [
            "武将確定" => [
                ['確率'=>100,  'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]]
            ],
            "宝箱確定" => [
                ['確率'=>100,  'slots' => ['武将'=> 0,'武運'=> 5, 'アイテム'=> 0, '宝箱'=> 1 ]]
            ],
            "通常"     => [
                ['確率'=>10.5, 'slots' => ['武将'=> 0,'武運'=> 2, 'アイテム'=> 4, '宝箱'=> 0 ]],
                ['確率'=>35.0, 'slots' => ['武将'=> 0,'武運'=> 3, 'アイテム'=> 3, '宝箱'=> 0 ]],
                ['確率'=>24.5, 'slots' => ['武将'=> 0,'武運'=> 4, 'アイテム'=> 2, '宝箱'=> 0 ]],
                ['確率'=> 4.5, 'slots' => ['武将'=> 1,'武運'=> 2, 'アイテム'=> 3, '宝箱'=> 0 ]],
                ['確率'=>15.0, 'slots' => ['武将'=> 1,'武運'=> 3, 'アイテム'=> 2, '宝箱'=> 0 ]],
                ['確率'=>10.5, 'slots' => ['武将'=> 1,'武運'=> 4, 'アイテム'=> 1, '宝箱'=> 0 ]],
            ],
        ],
    ],
    [
        'gacha_key' => 'pickup6',
        'gacha_name' => '星6ピックアップ',
        'gacha_sets' => [
            1 => ['gachaMode' => '通常', 'gachaType' => '通常'],
        ],
        'gacha_type_slots' => [
            "通常" => [
                [ "確率" => 100, "slots" => [ "*" => 6 ]],
            ],
        ],
    ],
    [
        'gacha_key' => 'sr_pickup',
        'gacha_name' => 'SRピックアップ',
        'gacha_sets' => [
            1 => ['gachaMode' => '通常', 'gachaType' => '通常'],
        ],
        'gacha_type_slots' => [
            "通常" => [
                [ "確率" => 100, "slots" => [ "*" => 10 ]],
            ],
        ],
    ],
];


