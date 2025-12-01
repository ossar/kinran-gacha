<?php
define('APP_ROOT', dirname(__DIR__));
define('CONFIG_DIR', APP_ROOT.'/config');
define('DATA_DIR', APP_ROOT.'/dat');

require_once APP_ROOT.'/vendor/autoload.php';

$res = require CONFIG_DIR.'/gacha_config.php';
define('GACHA_CONFIG', $res);

/**
 * ガチャキー
 *   - rankup5
 *   - hyakuren
 *   - gokubushin
 * ガチャ排出内容
 *   - gacha_contents.tsv
 *   - gacha_contents_hyakuren.tsv
 *   - gacha_contents_hyakuren2.tsv
 *   - gacha_contents_gokubushin.tsv
 */
function gachaObj($gachaKey, $contentFile) {
    $config = getConfig($gachaKey, GACHA_CONFIG);
    $gacha = new \MyApp\Gacha();
    $gacha->gachaKey = $config['gacha_key'];
    $gacha->gachaName = $config['gacha_name'];
    $gacha->gachaTypeSlots = $config['gacha_type_slots'];
    $gacha->gachaSets = $config['gacha_sets'];
    $gacha->loadGachaData(CONFIG_DIR.'/'.$contentFile);
    return $gacha;
}

/**
 * 対象のコンフィグを取得する
 */
function getConfig($key, $gachaConfig) {
    $res = array_values(array_filter($gachaConfig, function($item) use ($key) {
        return isset($item['gacha_key']) && $item['gacha_key'] == $key;
    }));
    return $res ? $res[0]: false;
}


