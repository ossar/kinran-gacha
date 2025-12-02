<?php
require_once __DIR__.'/init.php';

$gachaKey = 'rankup5';
$contentFile = 'gacha_contents_rankup5.tsv';
$gacha = gachaObj($gachaKey, $contentFile);


$list = $gacha->getItemList();
print_r($list);

$buunList = $gacha->getBuunNames($list);
print_r($buunList);
