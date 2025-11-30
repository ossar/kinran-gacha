<?php
namespace MyApp;

class Gacha {

    // ガチャセット数
    public $gachaSets;
    // ガチャタイプ毎のスロット数情報
    public $gachaTypeSlots;
    // ガチャモード毎のガチャ内容
    public $gachaModeContents;

    public function loadGachaData($filename) {
        $dat = [];
        $keys = [];
        $fp = fopen($filename, "r");
        while (FALSE !== $line=fgets($fp)) {
            $line = rtrim($line, "\r\n");
            $cols = explode("\t", $line);
            if (!$keys) {
                $keys = $cols;
                continue;
            }
            $buf = [];
            foreach ($cols as $idx => $val) {
                $buf[$keys[$idx]] = $val;
            }
            if (empty($dat[$buf['ガチャモード']])) {
                $dat[$buf['ガチャモード']] = [];
            }
            $dat[$buf['ガチャモード']][$buf['排出タイプ']][] = [
                '排出タイプ' => $buf['排出タイプ'],
                '排出内容' => $this->parseContent($buf['排出内容'], $buf['排出タイプ']),
                '確率' => $this->parseProb($buf['確率']),
            ];
        }
        fclose($fp);
        $this->gachaModeContents = $dat;
    }

    public function parseProb(string $prob) {
        $res = rtrim($prob, "%");
        return floatval($res);
    }

    public function parseContent(string $itemStr, string $type) {
        $dat = [
            'itemKey' => "{$type}:{$itemStr}",
            'type' => $type,
            'name' => null,
            'num'  => null,
            'rank' => null,
        ];
        switch ($type) {
        case '武将':
            $buf = explode(" ", $itemStr);
            $dat['name'] = trim($buf[0]);
            $dat['rank'] = trim($buf[1]);
            $dat['num']  = 1;
            break;
        case '武運':
            $buf = explode('x', $itemStr);
            $dat['name'] = trim($buf[0]);
            $dat['num'] = intval(trim($buf[1]));
            break;
        case '宝箱':
            $dat['name'] = $itemStr;
            $dat['num']  = 1;
            if (preg_match('/(星\d)/u', $itemStr, $match)) {
                $dat['rank'] = $match[1];
            }
            break;
        case 'アイテム':
            $dat['name'] = $itemStr;
            $dat['num']  = 1;
            break;
        default:
            var_dump($type);
            var_dump($itemStr);
            throw new RuntimeException('Unknown item type.');
            break;
        }
        return $dat;
    }

    /**
     * ガチャで出現する武運の武将一覧を取得する
     *
     * @param    array   排出内容一覧
     * @return   array   武将名一覧
     */
    public function getBuunKeys():array {
        $buunKeys = [];
        foreach ($this->gachaModeContents as $mode => $row) {
            foreach ($row as $itemType => $items) {
                foreach ($items as $item) {
                    switch ($itemType) {
                    case '武運':
                        $name = $item['排出内容']['name'];
                        $buunKeys[$name] = $name;
                        break;
                    case '武将':
                        $name = $item['排出内容']['name'];
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
                    foreach ($this->gachaModeContents[$gachaMode] as $type => $items) {
                        foreach ($items as $row) {
                            $itemProb = $row['確率'];
                            $itemKey = $row['排出内容']['itemKey'];
                            $exp = $slotProb / 100 * $itemProb / 100 * $slotCount * 1;
                            if (empty($expct[$itemKey])) {
                                $expct[$itemKey] = 0;
                            }
                            $expct[$itemKey] += $exp;
                        }
                    }
                } else {
                    foreach ($this->gachaModeContents[$gachaMode][$itemType] as $row) {
                        $itemProb = $row['確率'];
                        $itemKey = $row['排出内容']['itemKey'];
                        $exp = $slotProb / 100 * $itemProb / 100 * $slotCount * 1;
                        if (empty($expct[$itemKey])) {
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
     * ガチャを最後まで引き切る
     * @return    array    [ 期待値配列, 武運期待値配列 ]
     */
    public function batchGacha():array {
        $collects = [];
        $colBuun = [];
        foreach ($this->gachaSets as $idx => $row) {
            $gachaMode = $row['gachaMode'];
            $gachaType = $row['gachaType'];
            $list = $this->pull($gachaMode, $gachaType);
            foreach ($list as $row) {
                $item = $row['排出内容'];
                if ($res = $this->getBuun($item)) {
                    if ($res[0] && $res[1]) {
                        if (empty($colBuun[$res[0]])) {
                            $colBuun[$res[0]] = 0;
                        }
                        $colBuun[$res[0]] += $res[1];
                    }
                }
                $key = $item['itemKey'];
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
     * ガチャを1回（1セット）引く
     */
    public function pull(string $gachaMode, string $gachaType) {
        // スロットの決定
        $probs  = array_column($this->gachaTypeSlots[$gachaType], '確率');
        $values = array_values($this->gachaTypeSlots[$gachaType]);
        $idx = $this->getProbItems($probs);
        $slot = $values[$idx];
        $list = [];
        foreach ($slot['slots'] as $itemType => $count) {
            if ($itemType == "*") {
                $items = [];
                foreach ($this->gachaModeContents[$gachaMode] as $type => $rows) {
                    foreach ($rows as $row) {
                        $items[] = $row;
                    }
                }
                $probs  = array_column($items, '確率');
                $values = array_values($items);
                for ($i=0; $i<$count; $i++) {
                    $idx = $this->getProbItems($probs);
                    $list[] = $values[$idx];
                }
            } else {
                for ($i=0; $i<$count; $i++) {
                    $probs  = array_column($this->gachaModeContents[$gachaMode][$itemType], '確率');
                    $values = array_values($this->gachaModeContents[$gachaMode][$itemType]);
                    $idx = $this->getProbItems($probs);
                    $list[] = $values[$idx];
                }
            }
        }
        return $list;
    }

    /**
     * くじを引く
     * 合計が100になる配列を受け取り、確率にそってindexを返す
     * @param    array    [ 20, 30, 15, 25, 10 ] :合計100
     * @return   int|bool   index 3
     */
    public function getProbItems(array $probs):int|bool {
        if (!$probs) {
            return false;
        }
        $maxNum = 1000000;
        $rand = random_int(0, $maxNum)/$maxNum * 100;
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

    /**
     * アイテムの武運換算数を取得する
     *
     */
    public function getBuun(array $item):array|bool {
        $name = '';
        $buun = 0;
        switch ($item['type']) {
        case '武将':
            $name = $item['name'];
            switch ($item['rank']) {
            case '星1':
                $buun = 15;
                break;
            case '星3':
                $buun = 44;
                break;
            case '星4':
                $buun = 140;
                break;
            case '星5':
                $buun = 340;
                break;
            case '星6':
                $buun = 620;
                break;
            default:
                throw new RuntimeException();
                break;
            }
            break;
        case '武運':
            $name = $item['name'];
            $buun = $item['num'];
            break;
        case '宝箱':
            $name = '選択宝箱';
            switch ($item['rank']) {
            case '星3':
                $buun = 44;
                break;
            case '星4':
                $buun = 140;
                break;
            case '星5':
                $buun = 340;
                break;
            case '星6':
                $buun = 620;
                break;
            default:
                throw new RuntimeException();
                break;
            }
            break;
        case 'アイテム':
            break;
        default:
            var_dump($item);
            throw new RuntimeException();
            break;
        }
        if (!$name || !$buun) {
            return false;
        }
        return [$name, $buun];
    }


}
