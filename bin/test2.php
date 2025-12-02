<?php
require_once __DIR__.'/init.php';

$gachaKey = 'hyakuren';
$contentFile = 'gacha_contents_hyakuren.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

echo $gacha->gachaKey, "\n", $gacha->gachaName, "\n\n";

print_r($gacha->getItemList());
print_r($gacha->getGachaExpects('通常', '通常'));

