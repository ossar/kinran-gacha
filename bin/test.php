<?php

require_once __DIR__.'/include.php';

$mode = "通常";
$type = "通常";

$mode = "宝箱星5箱確定";
$type = "宝箱確定";

$res = $gacha->pull($mode, $type);
print_r($res);
