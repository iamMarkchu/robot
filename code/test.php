<?php
require dirname(dirname(__FILE__)). '/config/app.php';
use Lib\CouponApi;

$api = new CouponApi();
$user = $api->request('/api/user', 'GET');
print_r($user);
