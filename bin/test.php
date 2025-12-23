<?php
namespace MyApp;

use Symfony\Component\Yaml\Yaml;
use RuntimeException;

require_once __DIR__.'/init.php';


$file = CONFIG_DIR.'/gacha_config.yaml';

if (!is_file($file)) {
    throw new RuntimeException('no config file');
}

$conf = Yaml::parseFile($file);

foreach ($conf as $row) {
    echo "{$row['key']}「{$row['name']}」\n";
    foreach ($row['sets'] as $key2 => $row2) {
        echo "{$key2} : {$row2['mode']} {$row2['slot_type']}\n";
    }
    foreach ($row['slot_type'] as $key2 => $row2) {
        echo "{$key2} :\n";
        foreach ($row2 as $row3) {
            echo "   [{$row3['prob']}] ";
            echo implode(" ", array_map(function($k , $v) {
                return "{$k}:{$v}";
            } , array_keys($row3['slots']), $row3['slots']));
            echo "\n";
        }
        echo "\n";
    }
    echo "\n";
}
