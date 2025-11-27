<?php
require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/config.php';


$gachaContentsFile = __DIR__.'/gacha_contents.tsv';


$gacha = new \MyApp\Gacha();

$gacha->gachaTypeSlots = $gachaTypeSlots;
$gacha->gachaSets = $gachaSets;
$gacha->loadGachaData($gachaContentsFile);

$gachaModeContents = $gacha->gachaModeContents;


