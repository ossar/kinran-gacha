<?php
declare(strict_types=1);

namespace MyApp;

define('MyApp\APP_ROOT', dirname(__DIR__));
define('MyApp\CONFIG_DIR', APP_ROOT.'/config');
define('MyApp\DATA_DIR', APP_ROOT.'/dat');

require_once APP_ROOT.'/vendor/autoload.php';

define('MyApp\GACHA_CONFIG', require CONFIG_DIR.'/gacha_config.php');
