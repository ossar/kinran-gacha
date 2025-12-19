<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'sr_pickup';
$contentFile = 'gacha_contents_sr_pickup.tsv';
$proc = new GachaCommand($gachaKey, $contentFile);

$count = 1000;
$setNum = 100;

$outFile = sprintf("out-%s-%03d.tsv", $gachaKey, $setNum);
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
for ($i=0; $i<$count; $i++) {
    list($coll, $collBuun) = $proc->pullNumTimes($setNum);
    if ($i==0) {
        $line = implode("\t", array_keys($collBuun))."\n";
        fwrite($fp, $line);
        echo $line;
    }
    $line = implode("\t", $collBuun)."\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
