<?php
namespace MyApp;

use MyApp\Entity\Gacha;
use RuntimeException;
use MathPHP\Statistics\Descriptive;
use MathPHP\Statistics\Average;
use Symfony\Component\Yaml\Yaml;

require_once __DIR__.'/init.php';

$gacha_config = Yaml::parseFile(CONFIG_DIR.'/gacha_config.yaml');

$gachaList = [
    ['gokubushin' , 'out-gokubushin-050.tsv' ,  50 ],
    ['hyakuren'   , 'out-hyakuren2.tsv'      ,   5 ],
    ['pickup6'    , 'out-pickup6-100.tsv'    , 100 ],
    ['rankup5'    , 'out-rankup5.tsv'        ,  10 ],
    ['rankup6'    , 'out-rankup6.tsv'        ,  15 ],
    ['re_pickup'  , 'out-re_pickup-010.tsv'  ,  10 ],
    ['re_pickup'  , 'out-re_pickup-020.tsv'  ,  20 ],
    ['re_pickup'  , 'out-re_pickup-050.tsv'  ,  50 ],
    ['re_pickup'  , 'out-re_pickup-100.tsv'  , 100 ],
    ['sr_pickup'  , 'out-sr_pickup-100.tsv'  , 100 ],
    ['gokubushin_denrimi' , 'out-gokubushin_denrimi-023.tsv' ,  23 ],
];

ob_start();
$init = 1;
foreach ($gachaList as $row) {
    $config = Gacha::getConfig($row[0], $gacha_config);
    $f = $row[1];
    $sets = $row[2];
    if (!$fp = fopen(DATA_DIR.'/'.$f, 'r')) {
        throw new RuntimeException();
    }
    $keys = [];
    $dat = [];
    $analyze = [];
    while (FALSE!==$line=fgets($fp)) {
        $line = rtrim($line, "\r\n");
        if (!$line) {
            continue;
        }
        $cols = explode("\t", $line);
        if (!$keys) {
            $keys = $cols;
            continue;
        }
        foreach ($cols as $idx => $val) {
            $key = $keys[$idx];
            $dat[$key][] = (float)$val;
        }
    }
    fclose($fp);

    foreach ($dat as $key => $rows) {
        $analyze[$key] = [
            'average' => Average::mean($rows),
            'median' => Average::median($rows),
            'min' => min($rows),
            'max' => max($rows),
            'per30'   => Descriptive::percentile($rows, 30),
            'per70'   => Descriptive::percentile($rows, 70),
        ];
    }
    foreach ($analyze as $key => $rows) {
        if ($init) {
            $init = 0;
            echo sprintf("%s\t%s\t%s\t%s\t%s\t%s\n"
                , 'file'
                , 'key'
                , 'sets'
                , 'name'
                , 'buun'
                , implode("\t", array_keys($rows))
            );
        }
        echo sprintf("%s\t%s\t%s\t%s\t%s\t%s\n"
            , $f
            , $config['key']
            , $sets
            , $config['name']
            , $key
            , implode("\t", $rows)
        );
    }
}
$outfile = DATA_DIR.'/stat.tsv';
$output = ob_get_clean();
file_put_contents($outfile, $output);

echo $output, "\n";
echo $outfile, "\n";



