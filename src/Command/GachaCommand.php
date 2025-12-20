<?php
namespace MyApp\Command;

use MyApp\Entity\Gacha;
use const MyApp\{GACHA_CONFIG, CONFIG_DIR};

class GachaCommand {

    public $gacha;

    public function __construct(
        protected string $gachaKey,
        protected string $contentFile
    ) {
        $this->gacha = $this->getGacha($gachaKey, $contentFile);

    }

    public function getGacha(string $gachaKey, string $contentFile):object {
        $config = $this->getConfig($gachaKey, GACHA_CONFIG);
        $gacha = new Gacha;
        $gacha->gachaKey = $config['gacha_key'];
        $gacha->gachaName = $config['gacha_name'];
        $gacha->gachaTypeSlots = $config['gacha_type_slots'];
        $gacha->gachaSets = $config['gacha_sets'];
        $gacha->loadGachaModeItems(CONFIG_DIR.'/'.$contentFile);
        return $gacha;
    }

    /**
     * @param  string $key
     * @param  array  $gachaConfig
     */
    public function getConfig(string $key, array $gachaConfig):array|bool {
        $res = array_values(array_filter($gachaConfig, function($item) use ($key) {
            return isset($item['gacha_key']) && $item['gacha_key'] == $key;
        }));
        return $res ? $res[0]: false;
    }

    /**
     * 規定セット数の期待値を取得する
     */
    public function getTotalExpect():array {
        $memo = [];
        $expct = [];
        foreach ($this->gacha->gachaSets as $idx => $row) {
            $gachaMode = $row['gachaMode'];
            $gachaType = $row['gachaType'];
            if (isset($memo[$gachaMode])) {
                $res = $memo[$gachaMode];
            } else {
                $res = $this->gacha->getGachaExpects($gachaMode, $gachaType);
                $memo[$gachaMode] = $res;
            }
            foreach ($res as $key => $val) {
                if (!isset($expct[$key])) {
                    $expct[$key] = 0;
                }
                $expct[$key] += $val;
            }
        }
        return $expct;
    }

    /**
     * @param  array $expect
     * @param  array $itemList
     * @return array $buunExpect
     */
    public function getBuunExpect(array $expect, array $itemList):array {
        $buunExpect = [];
        foreach ($expect as $key => $exp) {
            if (!$res = $itemList[$key]->getItemBuun()) {
                continue;
            }
            list($name, $buun) = $res;
            if (!isset($buunExpect[$name])) {
                $buunExpect[$name] = 0;
            }
            $buunExpect[$name] += $exp * $buun ;
        }
        return $buunExpect;
    }

    /**
     * @return array $ret
     */
    public function pullNumTimes(int $num):array {
        static $defColl = [], $defCollBuun = [];
        if (empty($defColl)) {
            $itemList = $this->gacha->getItemList();
            foreach ($itemList as $key => $row) {
                $defColl[$key] = 0;
            }
            $buunNames = $this->gacha->getBuunNames($itemList);
            foreach ($buunNames as $name) {
                $defCollBuun[$name] = 0;
            }
        }
        $ret = [$defColl, $defCollBuun];
        for ($i=0; $i<$num; $i++) {
            list($col, $bun) = $this->gacha->batchGacha();
            foreach ($col as $key => $val) {
                $ret[0][$key] += $val['count'];
            }
            foreach ($bun as $key => $val) {
                $ret[1][$key] += $val;
            }
        }
        return $ret;
    }


}
