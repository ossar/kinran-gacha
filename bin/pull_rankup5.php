<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup5';
$contentFile = CONFIG_DIR.'/gacha_contents_rankup5.tsv';
$gacha = GachaCommand::getGacha($gachaKey, $contentFile);

$repeatCount = 1000;

$outFile = "out-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
for ($i=0; $i<$repeatCount; $i++) {
    list($coll, $collBuun) = $gacha->pullNumTimes(1);
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
