<?php
namespace MyApp;

use InvalidArgumentException;

class GachaItem {

    public string $key;
    public int|null $num = null;
    public string|null $name = null;
    public string|null $rarity = null;
    public string|int|null $rank = null;

    public function __construct(
        public string $type,
        public string $label
    ) {
        $this->key = $this->makeItemKey($type, $label);
        $this->parseLabel();
    }

    public static function makeItemKey(string $type, string $label):string {
        return "{$type}:{$label}";
    }

    public function getItemBuun():array|bool {
        switch ($this->type) {
        case '武将':
            $rarityRankBuun = [
                'N'   => [1 => 8 , 2 => 23, 3 => 53, 4 => 133, 5 => 263, ],
                'R'   => [1 => 12, 2 => 36, 3 => 81, 4 => 181, 5 => 331, ],
                'SR'  => [1 => 15, 2 => 44, 3 => 60, 4 => 120, 5 => 290, ],
                'UR'  => [1 => 21, 2 => 32, 3 => 44, 4 => 140, 5 => 340, 6 => 620 ],
                'UR2' => [1 => 21, 2 => 32, 3 => 60, 4 => 140, 5 => 340, 6 => 620 ],
            ];
            return isset($this->rarity, $this->rank) ? [$this->name, $rarityRankBuun[$this->rarity][$this->rank]] : false;
        case '武運':
            return [$this->name, $this->num];
        case '宝箱':
            $rankBuun = [1 => 21, 2 => 32, 3 => 44, 4 => 140, 5 => 340, 6 => 620 ];
            return isset($this->rank) ? ['選択宝箱', $rankBuun[$this->rank]]: false;
        default:
            return false;
        }
    }

    /**
     *
     */
    public function parseLabel():void {
        switch ($this->type) {
        case '武将':
            if (!preg_match('/(N|R|SR|UR|UR2)?(.+)星(\d)/u', $this->label, $match)) {
                throw new InvalidArgumentException("Unkown format:  [{$this->type}] {$this->label}");
            }
            $this->num = 1;
            $this->rarity = $match[1];
            $this->name = trim($match[2]);
            $this->rank = $match[3];
            break;
        case '武運':
            if (!preg_match('/(.+)x(\d+)/u', $this->label, $match)) {
                throw new InvalidArgumentException("Unkown format:  [{$this->type}] {$this->label}");
            }
            $this->num = (int)$match[2];
            $this->name = trim($match[1]);
            break;
        case '宝箱':
            if (!preg_match('/星(\d)宝?箱/u', $this->label, $match)) {
                throw new InvalidArgumentException("Unkown format:  [{$this->type}] {$this->label}");
            }
            $this->num = 1;
            $this->name = $match[0];
            $this->rank = $match[1];
            break;
        case 'アイテム':
            if (preg_match('/(.+)x(\d+)/u', $this->label, $match)) {
                $this->num = (int)$match[2];
                $this->name = trim($match[1]);
            } else {
                $this->num = 1;
                $this->name = trim($this->label);
            }
            break;
        default:
            throw new InvalidArgumentException("Unkown type. type={$this->type} label={$this->label}\n");
        }
    }


}
