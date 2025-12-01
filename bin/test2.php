<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
$gachaConfig = require dirname(__DIR__).'/config/gacha_config.php';

$gachaDataFiles = [
    'gacha_contents.tsv',
    'gacha_contents_hyakuren.tsv',
    'gacha_contents_hyakuren2.tsv',
];

$dataFile = dirname(__DIR__).'/config/'.$gachaDataFiles[2];

$config = $gachaConfig[1];


$gacha = new \MyApp\Gacha();
$gacha->gachaTypeSlots = $config['gacha_type_slots'];
$gacha->gachaSets = $config['gacha_sets'];
$gacha->loadGachaData($dataFile);

print_r($gacha->getItemList());

$expct = $gacha->getGachaExpects('通常', '通常');
print_r($expct);
