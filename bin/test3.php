<?php
require_once __DIR__.'/init.php';

use MyApp\Gacha2;
use MyApp\GachaItem;
#use InvalidArgumentException;


$gacha = new Gacha2;
$gacha->loadGachaModeItems(CONFIG_DIR.'/gacha_contents_rankup6.tsv');


print_r($gacha);
exit;






function parseProb (string $str):float {
    return floatval(rtrim($str, '%'));
}





$gachaModeItems = [];

$items = [];
$fp = fopen(CONFIG_DIR.'/gacha_contents_rankup6.tsv', "r");
$head = 0;
while (FALSE!==$line = fgets($fp)) {
    $line = rtrim($line, "\r\n");
    list($mode, $type, $label, $prob) = explode("\t", $line);
    if (!$head) {
        $head = 1;
        continue;
    }
    $item = new GachaItem($type, $label);
    $gachaModeItems[$mode][] = [
        'type' => $type,
        'key'  => $item->key,
        'item' => $item,
        'prob' => parseProb($prob),
    ];

}
fclose($fp);

foreach ($gachaModeItems as $mode => $rows) {
    foreach ($rows as $row) {
        echo sprintf("%s %s \t %s  %f\n"
            , $mode
            , $row['type']
            , $row['item']->key
            , $row['prob']
        );
    }
}


exit;

$type = '武将';
$label = 'URホカク星3';


