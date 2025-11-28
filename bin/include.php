<?php
require_once dirname(__DIR__).'/vendor/autoload.php';
require_once dirname(__DIR__).'/config/config.php';

$gachaContentsFile = dirname(__DIR__).'/config/gacha_contents.tsv';

$gacha = new \MyApp\Gacha();
$gacha->gachaTypeSlots = $gachaTypeSlots;
$gacha->gachaSets = $gachaSets;
$gacha->loadGachaData($gachaContentsFile);

