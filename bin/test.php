<?php
namespace MyApp;

use MyApp\Entity\Gacha;

require_once __DIR__.'/init.php';

$key = 'rankup6';
$file = CONFIG_DIR.'/gacha_contents_rankup6.tsv';
$gacha = new Gacha($key, $file);


$items = $gacha->getItemList();

foreach ($items as $k => $v) {
    echo sprintf("%s\t[box:%s]\t(%s)\n"
        , $v->type
        , $v->boxType
        , $k
    );
}

