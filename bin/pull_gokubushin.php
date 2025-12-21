<?php
namespace MyApp;

use MyApp\Entity\Gacha;

require_once __DIR__.'/init.php';

$gachaKey = 'gokubushin';
$contentFile = CONFIG_DIR.'/gacha_contents_gokubushin.tsv';
$gacha = new Gacha($gachaKey, $contentFile);

$count = 1000;
$setNum = 50;

$outFile = sprintf("out-%s-%03d.tsv", $gachaKey, $setNum);
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
for ($i=0; $i<$count; $i++) {
    list($coll, $collBuun) = $gacha->pullNumTimes($setNum);
    if ($i==0) {
        $line = implode("\t", array_keys($collBuun))."\n";
        echo $line;
        fwrite($fp, $line);
    }
    $line = implode("\t", $collBuun)."\n";
    echo $line;
    fwrite($fp, $line);
}
fclose($fp);

echo "\n{$outFile}\n";
