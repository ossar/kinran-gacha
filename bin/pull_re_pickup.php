<?php
namespace MyApp;

use MyApp\Entity\Gacha;

require_once __DIR__.'/init.php';

$gachaKey = 're_pickup';
$contentFile = CONFIG_DIR.'/gacha_contents_re_pickup.tsv';
$gacha = new Gacha($gachaKey, $contentFile);

$count = 1000;
$setNum = 10;

foreach ([10, 20, 50, 100] as $setNum) {
    $outFile = sprintf("out-%s-%03d.tsv", $gachaKey, $setNum);
    $fp = fopen(DATA_DIR.'/'.$outFile, "w");
    for ($i=0; $i<$count; $i++) {
        list($coll, $collBuun) = $gacha->pullNumTimes($setNum);
        if ($i==0) {
            $line = implode("\t", array_keys($collBuun))."\n";
            fwrite($fp, $line);
            #echo $line;
        }
        $line = implode("\t", $collBuun)."\n";
        fwrite($fp, $line);
        #echo $line;
    }
    fclose($fp);
    echo "\n{$outFile}\n";
}

