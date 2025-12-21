<?php
namespace MyApp;

use MyApp\Command\GachaCommand;

require_once __DIR__.'/init.php';

$gachaKey = 'rankup6';
$contentFile = CONFIG_DIR.'/gacha_contents_rankup6.tsv';
$gacha = GachaCommand::getGacha($gachaKey, $contentFile);

$expct = $gacha->getTotalExpect();
$itemList = $gacha->getItemList();
$buunExpct = $gacha->getBuunExpect($expct, $itemList);

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
