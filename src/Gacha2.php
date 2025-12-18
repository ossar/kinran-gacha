<?php
namespace MyApp;

use MyApp\GachaItem;

class Gacha2 {

    public $gachaKey;
    public $gachaName;
    public $gachaSets;
    public $gachaTypeSlots;
    public $gachaModeItems;

    public function loadGachaModeItems(string $filename):void {
        $dat = [];
        $fp = fopen($filename, "r");
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

    public function parseProb(string $prob):string {
        return floatval(rtrim($prob, '%'));
    }


}
