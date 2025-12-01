<?php
require_once __DIR__.'/init.php';

$gachaKey = 'hyakuren';
$contentFile = 'gacha_contents_hyakuren.tsv';
$gacha = gachaObj($gachaKey, $contentFile);

echo $gacha->gachaKey, "\n";
echo $gacha->gachaName, "\n";
echo "\n";

print_r($gacha->getItemList());
$expct = $gacha->getGachaExpects('通常', '通常');
print_r($expct);

