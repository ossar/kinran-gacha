<?php
namespace MyApp;

use MyApp\GachaItem;
use RuntimeException;

class Gacha2 {

    public string $gachaKey = '';
    public string $gachaName = '';
    public array $gachaSets = [];
    public array $gachaTypeSlots = [];
    public array $gachaModeItems = [];

    public function loadGachaModeItems(string $filename):void {
        $dat = [];
        if (!$fp = fopen($filename, "r")) {
            throw new RuntimeException("cannot open file. {$filename}");
        }
        ;
        $head = 0;
        while (FALSE !== $line=fgets($fp)) {
            $line = rtrim($line, "\r\n");
            if (!$line) {
                continue;
            }
            list($mode, $type, $label, $prob) = explode("\t", $line);
            if (!$head) {
                $head=1;
                continue;
            }
            $item = new GachaItem($type, $label);
            $dat[$mode][$type][] = [
                'type' => $type,
                'item' => $item,
                'prob' => $this->parseProb($prob),
            ];
        }
        fclose($fp);
        $this->gachaModeItems = $dat;
    }

    public function parseProb(string $prob):float {
        return floatval(rtrim($prob, '%'));
    }

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
     * @param   array  $items
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
     *
     *
     */
    public function getBuunKeys():array {
        $buunKeys = [];
        foreach ($this->gachaModeItems as $mode => $row) {
            foreach ($row as $itemType => $items) {
                foreach ($items as $col) {
                    $item = $col['item'];
                    switch ($item->type) {
                    case '武運':
                        $name = $item->name;
                        $buunKeys[$name] = $name;
                        break;
                    case '武将':
                        $name = $item->name;
                        $buunKeys[$name] = $name;
                        break;
                    case '宝箱':
                        $name = '選択宝箱';
                        $buunKeys[$name] = $name;
                        break;
                    default:
                        break;
                    }
                }
            }
        }
        return array_values($buunKeys);
    }
    /**
     * ガチャ1セットの期待値を出す
     *
     * @param   array   排出内容
     * @param   array   スロット一覧
     * @return  array   [アイテムのキー=>期待値] の配列
     */
    public function getGachaExpects(string $gachaMode, string $gachaType) {
        $expct = [];
        foreach ($this->gachaTypeSlots[$gachaType] as $slot) {
            $slotProb = $slot['確率'];
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
     *
     * @return    array
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
                    $collects[$key] = [
                        'count' => 0,
                        'item'  => $item,
                    ];
                }
                $collects[$key]['count']++;
            }
        }
        return [$collects, $colBuun];
    }

    /**
     *
     */
    public function pull(string $gachaMode, string $gachaType):array {
        // スロットの決定
        $probs  = array_column($this->gachaTypeSlots[$gachaType], '確率');
        $values = array_values($this->gachaTypeSlots[$gachaType]);
        $idx = $this->getProbItems($probs);
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
                $values = array_values($items);
                for ($i=0; $i<$count; $i++) {
                    $idx = $this->getProbItems($probs);
                    $list[] = $values[$idx];
                }
            } else {
                for ($i=0; $i<$count; $i++) {
                    $probs  = array_column($this->gachaModeItems[$gachaMode][$itemType], 'prob');
                    $values = array_values($this->gachaModeItems[$gachaMode][$itemType]);
                    $idx = $this->getProbItems($probs);
                    $list[] = $values[$idx];
                }
            }
        }
        return $list;
    }

    /**
     *
     * @param    array    $probs
     */
    public function getProbItems(array $probs):int|bool {
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
