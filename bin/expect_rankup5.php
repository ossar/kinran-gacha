<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup5';
$contentFile = 'gacha_contents_rankup5.tsv';

$proc = new GachaCommand($gachaKey, $contentFile);
$expct = $proc->getTotalExpect();
$itemList = $proc->gacha->getItemList();
$buunExpct = $proc->getBuunExpect($expct, $itemList);

echo "=========アイテムの期待値========\n";
foreach ($expct as $key => $val) {
    echo sprintf("%s\t%s\n"
        , $key
        , $val
    );
}
echo "\n";

echo "=========武運期待値========\n";
$outFile = "expect-{$gachaKey}.tsv";
$fp = fopen(DATA_DIR.'/'.$outFile, 'w');
foreach ($buunExpct as $name => $exp) {
    $line = "{$name}\t{$exp}\n";
    fwrite($fp, $line);
    echo $line;
}
fclose($fp);

echo "\n{$outFile}\n";
