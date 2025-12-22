<?php
declare(strict_types=1);

namespace MyApp\Entity;

use MyApp\Entity\GachaItem;
use RuntimeException;

class Gacha {

    public string $gachaKey = '';

    public string $gachaName = '';

    /**
     * ガチャのセット数・スロット内容の定義リスト
     * あらかじめ登録しておく
     * @var  array<mixed>
     */
    public static array $gacha_config = [];

    /**
     * ガチャのセット内容
     * @var  array<mixed>
     */
    public array $gachaSets = [];

    /**
     * ガチャのスロット内容
     * @var  array<mixed>
     */
    public array $gachaTypeSlots = [];

    /**
     * ガチャのアイテムデータ
     * @var  array<mixed>
     */
    public array $gachaModeItems = [];

    public function __construct(string $gachaKey, string $contentFile) {
        if (!$config = self::getConfig($gachaKey, self::$gacha_config)) {
            throw new RuntimeException("no config for {$gachaKey} {$contentFile}");
        }
        $this->gachaKey = $config['gacha_key'];
        $this->gachaName = $config['gacha_name'];
        $this->gachaTypeSlots = $config['gacha_type_slots'];
        $this->gachaSets = $config['gacha_sets'];
        $this->gachaModeItems = $this->parseGachaModeItems($contentFile);
    }

    /**
     * @param  string $key
     * @param  array<mixed>  $gachaConfig
     * @return array<mixed>|bool
     */
    public static function getConfig(string $key, array $gachaConfig):array|bool {
        $res = array_values(array_filter($gachaConfig, function($item) use ($key) {
            return isset($item['gacha_key']) && $item['gacha_key'] == $key;
        }));
        return $res ? $res[0]: false;
    }

    /**
     * @return  array<mixed>
     */
    public function parseGachaModeItems(string $filename, bool $head=true):array {
        $dat = [];
        if (!$fp = fopen($filename, "r")) {
            throw new RuntimeException("cannot open file. {$filename}");
        }
        $keys = [];
        while (FALSE !== $line=fgets($fp)) {
            $line = rtrim($line, "\r\n");
            if (!$line) {
                continue;
            }
            list($mode, $type, $label, $prob) = explode("\t", $line);
            if ($head && empty($keys)) {
                $keys[] = $mode;
                $keys[] = $type;
                $keys[] = $label;
                $keys[] = $prob;
                continue;
            }
            $item = new GachaItem($type, $label);
            $dat[$mode][$type][] = [
                'item' => $item,
                'prob' => $this->parseProb($prob),
            ];
        }
        fclose($fp);
        return $dat;
    }

    public function parseProb(string $prob):float {
        return floatval(rtrim($prob, '%'));
    }

    /**
     * @return   array<mixed>
     */
    public function getItemList():array {
        $list = [];
        foreach ($this->gachaModeItems as $mode => $rows) {
            foreach ($rows as $itemType => $items) {
                foreach ($items as $row) {
                    $item = $row['item'];
                    if (!isset($list[$item->key])) {
                        $list[$item->key] = $item;
                    }
                }
            }
        }
        return $list;
    }

    /**
     * @param   array<mixed>  $items
     * @return  array<string>
     */
    public function getBuunNames(array $items):array {
        $list = [];
        foreach ($items as $item) {
            switch ($item->type) {
            case '武将':
                $name = $item->name;
                $list[$name] = $name;
                break;
            case '武運':
                $name = $item->name;
                $list[$name] = $name;
                break;
            case '宝箱':
                $name = '選択宝箱';
                $list[$name] = $name;
                break;
            default:
                break;
            }
        }
        return array_values($list);
    }

    /**
     * ガチャ1セットの期待値を出す
     * @return  array<string, int>   [アイテムのキー=>期待値] の配列
     */
    public function getGachaExpects(string $gachaMode, string $gachaType):array {
        $expct = [];
        foreach ($this->gachaTypeSlots[$gachaType] as $slot) {
            $slotProb = $slot['prob'];
            foreach ($slot['slots'] as $itemType => $slotCount) {
                if (!$slotCount) {
                    continue;
                }
                if ($itemType == "*") {
                    foreach ($this->gachaModeItems[$gachaMode] as $type => $items) {
                        foreach ($items as $row) {
                            $itemProb = $row['prob'];
                            $itemKey = $row['item']->key;
                            $exp = $slotProb / 100 * $itemProb / 100 * $slotCount * 1;
                            if (!isset($expct[$itemKey])) {
                                $expct[$itemKey] = 0;
                            }
                            $expct[$itemKey] += $exp;
                        }
                    }
                } else {
                    foreach ($this->gachaModeItems[$gachaMode][$itemType] as $row) {
                        $itemProb = $row['prob'];
                        $itemKey = $row['item']->key;
                        $exp = $slotProb / 100 * $itemProb / 100 * $slotCount * 1;
                        if (!isset($expct[$itemKey])) {
                            $expct[$itemKey] = 0;
                        }
                        $expct[$itemKey] += $exp;
                    }
                }
            }
        }
        return $expct;
    }

    /**
     * @param  array<mixed> $expect
     * @param  array<mixed> $itemList
     * @return array<string, int> $buunExpect
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
     * 規定セット数の期待値を取得する
     * @return    array<string, int>
     */
    public function getTotalExpect():array {
        $memo = [];
        $expct = [];
        foreach ($this->gachaSets as $idx => $row) {
            $gachaMode = $row['gachaMode'];
            $gachaType = $row['gachaType'];
            if (isset($memo[$gachaMode])) {
                $res = $memo[$gachaMode];
            } else {
                $res = $this->getGachaExpects($gachaMode, $gachaType);
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
     * ガチャを最後まで引き切る
     * @return    array<mixed>
     */
    public function batchGacha():array {
        $collects = [];
        $colBuun = [];
        foreach ($this->gachaSets as $idx => $row) {
            $gachaMode = $row['gachaMode'];
            $gachaType = $row['gachaType'];
            $list = $this->pull($gachaMode, $gachaType);
            foreach ($list as $row) {
                $item = $row['item'];
                if ($res = $item->getItemBuun()) {
                    if (!isset($colBuun[$res[0]])) {
                        $colBuun[$res[0]] = 0;
                    }
                    $colBuun[$res[0]] += $res[1];
                }
                $key = $item->key;
                if (empty($collects[$key])) {
                    $collects[$key] = 0;
                }
                $collects[$key]++;
            }
        }
        return [$collects, $colBuun];
    }

    /**
     * ガチャを1回（1セット）引く
     * @return  array<mixed>
     */
    public function pull(string $gachaMode, string $gachaType):array {
        // スロットの決定
        $probs  = array_column($this->gachaTypeSlots[$gachaType], 'prob');
        $values = array_values($this->gachaTypeSlots[$gachaType]);
        $idx = self::getProbItems($probs);
        $slot = $values[$idx];
        $list = [];
        foreach ($slot['slots'] as $itemType => $count) {
            if ($itemType == "*") {
                $items = [];
                foreach ($this->gachaModeItems[$gachaMode] as $type => $rows) {
                    foreach ($rows as $row) {
                        $items[] = $row;
                    }
                }
                $probs  = array_column($items, 'prob');
                $values = $items;
                for ($i=0; $i<$count; $i++) {
                    $idx = self::getProbItems($probs);
                    $list[] = $values[$idx];
                }
            } else {
                for ($i=0; $i<$count; $i++) {
                    $probs  = array_column($this->gachaModeItems[$gachaMode][$itemType], 'prob');
                    $values = array_values($this->gachaModeItems[$gachaMode][$itemType]);
                    $idx = self::getProbItems($probs);
                    $list[] = $values[$idx];
                }
            }
        }
        return $list;
    }

    /**
     * @return array<mixed> $ret
     */
    public function pullNumTimes(int $num):array {
        static $defColl = [], $defCollBuun = [];
        if (empty($defColl)) {
            $itemList = $this->getItemList();
            foreach ($itemList as $key => $row) {
                $defColl[$key] = 0;
            }
            $buunNames = $this->getBuunNames($itemList);
            foreach ($buunNames as $name) {
                $defCollBuun[$name] = 0;
            }
        }
        $ret = [$defColl, $defCollBuun];
        for ($i=0; $i<$num; $i++) {
            list($col, $bun) = $this->batchGacha();
            foreach ($col as $key => $val) {
                $ret[0][$key] += $val;
            }
            foreach ($bun as $key => $val) {
                $ret[1][$key] += $val;
            }
        }
        return $ret;
    }

    /**
     * くじを引く
     * 合計が100になる配列を受け取り、確率にそってindexを返す
     * @param    array<number>      $probs
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
