<?php
declare(strict_types=1);

namespace MyApp\Command;

use MyApp\Entity\Gacha;

class GachaCommand {

    public static $gacha_config;

    public static function getGacha(string $gachaKey, string $contentFile):object {
        if (!$config = self::getConfig($gachaKey, self::$gacha_config)) {
            return false;
        }
        $gacha = new Gacha;
        $gacha->gachaKey = $config['gacha_key'];
        $gacha->gachaName = $config['gacha_name'];
        $gacha->gachaTypeSlots = $config['gacha_type_slots'];
        $gacha->gachaSets = $config['gacha_sets'];
        $gacha->loadGachaModeItems($contentFile);
        return $gacha;
    }

    /**
     * @param  string $key
     * @param  array  $gachaConfig
     */
    public static function getConfig(string $key, array $gachaConfig):array|bool {
        $res = array_values(array_filter($gachaConfig, function($item) use ($key) {
            return isset($item['gacha_key']) && $item['gacha_key'] == $key;
        }));
        return $res ? $res[0]: false;
    }

    /**
     * くじを引く
     * 合計が100になる配列を受け取り、確率にそってindexを返す
     * @param    array      $probs
     */
    public static function getProbItems(array $probs):int|bool {
        if (!$probs) {
            return false;
        }
        $maxNum = 1000000;
        $rand = random_int(0, $maxNum) * 100 / $maxNum;
        $sum = 0;
        $idx = 0;
        foreach ($probs as $prob) {
            $sum += $prob;
            if ($sum >= $rand) {
                break;
            }
            if ($idx >= sizeof($probs)-1) {
                break;
            }
            $idx++;
        }
        return $idx;
    }

}
