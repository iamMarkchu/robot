<?php
require dirname(dirname(__FILE__)). '/config/app.php';
use phpspider\core\phpspider;
use Lib\CrawlTask;
/* Do NOT delete this comment */
/* 不要删除这段注释 */
define('MARK_DEBUG', FALSE);
define('SITE_NAME', 'couponwitme');
define('SITE_URL', 'http://www.couponwitme.com');
define('SAVE_FILE_NAME', INCLUDE_ROOT . 'data/'. SITE_NAME.'_'.date('ymdhis'));
define('COMPETITOR_ID', 1);

$task = new CrawlTask(COMPETITOR_ID, SAVE_FILE_NAME);
$task->start();

$configs = array(
    'name' => SITE_NAME,
    'tasknum' => MARK_DEBUG ? 1: 8,
    'log_show' => MARK_DEBUG ? TRUE: FALSE,
    // 'save_running_state' => true,
    'queue_config' => $queue_config,
    'domains' => [
        'www.couponwitme.com'
    ],
    'scan_urls' => [
        'http://www.couponwitme.com/',
        'http://www.couponwitme.com/stores',
    ],
    'list_url_regexes' => [],
    'content_url_regexes' => [
        'http://www.couponwitme.com/vouchers/(.*)/',
    ],
    'fields' => [
        // 标题
        [
            'name' => "h1",
            'selector' => "//h1",
            'required' => TRUE,
        ],
        [
            'name' => "merchant_link",
            'selector' => "//div[contains(@id, 'store_screen')]/a[1]/@href",
            'required' => TRUE,
        ],
        [
            'name' => 'coupon_block',
            'selector' => "//div[@id='coupon_list']/div/div[contains(@class, 'coupon_block')]",
            'repeated' => TRUE,
            'children' => [
                [
                    'name' => 'coupon_id',
                    'selector' => "//p[contains(@class, 'coupon_title')]/a/@id",
                ],
                [
                    'name' => 'coupon_title',
                    'selector' => "//p[contains(@class, 'coupon_title')]/a",
                ],
                [
                    'name' => 'coupon_desc',
                    'selector' => "//span[starts-with(@id, 'coupondesc')]",
                ],
                [
                    'name' => 'coupon_restriction',
                    'selector' => "//span[contains(@class, 'coupon_restriction')]",
                ],
                [
                    'name' => 'coupon_link',
                    'selector' => "//a[starts-with(@id, 'divcover_')]/@href",
                ],
                [
                    'name' => 'expires',
                    'selector' => "//div[contains(@class, 'coupon_infor')]/p[3]",
                ],
                [
                    'name' => 'code',
                    'selector' => "//*[starts-with(@id, 'couponcode_')]",
                ],
                [
                    'name' => 'clicks',
                    'selector' => '//div[@class="click"]',
                ],
            ],
        ]
    ],
);
$spider = new phpspider($configs);
$spider->on_extract_page = function($page, $data)
{
    $data['page'] = $page['url'];
    $data['merchant_link'] = cwm_get_source_link($data['merchant_link']);
    foreach ($data['coupon_block'] as $k => $v) {
        // 处理id
        $data['coupon_block'][$k]['coupon_id'] = str_replace('coupontitle_', '', $v['coupon_id']);
        // 处理过期时间
        $tmpExpires = str_replace('Expires:', '', $v['expires']);
        if(strtolower(trim($tmpExpires)) == 'soon')
        {
            $data['coupon_block'][$k]['expired_at'] = '0000-00-00 00:00:00';
        }else{
            $data['coupon_block'][$k]['expired_at'] = date('Y-m-d 23:59:59', strtotime('+ '. $tmpExpires));
        }
        // 处理链接
        $data['coupon_block'][$k]['source_link'] = cwm_get_source_link($v['coupon_link']);
        unset($data['coupon_block'][$k]['coupon_link']);
        unset($data['coupon_block'][$k]['expires']);
        // 处理clicks
        if(!empty($v['clicks']))
        {
            $data['coupon_block'][$k]['clicks'] = str_replace(' clicks', '', $v['clicks']);
        }else{
            $data['coupon_block'][$k]['clicks'] = 0;
        }
    }
    $data = json_encode($data) . PHP_EOL;
    file_put_contents(SAVE_FILE_NAME, $data, FILE_APPEND | LOCK_EX);
    return false;
};
$spider->start();
$task->finish();
