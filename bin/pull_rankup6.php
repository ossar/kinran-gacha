<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup6';
$contentFile = 'gacha_contents_rankup6.tsv';
$proc = new GachaCommand($gachaKey, $contentFile);

$repeatCount = 1000;

$outFile = "out-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
for ($i=0; $i<$repeatCount; $i++) {
    list($coll, $collBuun) = $proc->pullNumTimes(1);
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
