<?php
namespace MyApp;

use RuntimeException;
use MathPHP\Statistics\Descriptive;
use MathPHP\Statistics\Average;

require_once __DIR__.'/init.php';

$files = [
    'out-gokubushin-050.tsv',
    'out-hyakuren.tsv',
    'out-hyakuren2.tsv',
    'out-pickup6-100.tsv',
    'out-rankup5.tsv',
    'out-rankup6.tsv',
    'out-re_pickup-010.tsv',
    'out-re_pickup-020.tsv',
    'out-re_pickup-050.tsv',
    'out-re_pickup-100.tsv',
    'out-sr_pickup-100.tsv',
];

foreach ($files as $f) {
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
    echo "{$f}\n";
    $init = 1;
    foreach ($analyze as $key => $rows) {
        if ($init) {
            $init = 0;
            echo "name\t".implode("\t", array_keys($rows))."\n";
        }
        echo $key."\t".implode("\t", $rows)."\n";
    }
    echo "\n";
}
