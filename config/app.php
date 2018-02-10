<?php
define('INCLUDE_ROOT', dirname(dirname(__FILE__)). '/');
require INCLUDE_ROOT . 'vendor/autoload.php';
define('TIME_ZONE', 'America/Los_Angeles');
date_default_timezone_set(TIME_ZONE);
$queue_config = [
    'host'      => '127.0.0.1',
    'port'      => 6379,
    'pass'      => '',
    'db'        => 5,
    'prefix'    => 'phpspider',
    'timeout'   => 30,
];

define('API_ROOT', 'http://xplan.mark');
define('STORAGE_ROOT', '/Users/mark/sites/xplan/storage/app/public');
