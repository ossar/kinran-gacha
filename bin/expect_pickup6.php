<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'pickup6';
$contentFile = 'gacha_contents_pickup6.tsv';

$proc = new GachaCommand($gachaKey, $contentFile);
$buunExpct = $proc->getTotalBuunExpect();

$outFile = "expct_{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, "w");
foreach ([10, 20, 50, 100] as $num) {
    $line = "{$num}セットの武運期待値\n";
    fwrite($fp, $line);
    echo $line;
    foreach ($buunExpct as $name => $buun) {
        $line = sprintf("%s\t%6.1f\n"
            , $name
            , $buun * $num
        );
        fwrite($fp, $line);
        echo $line;
    }
    $line = "\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
