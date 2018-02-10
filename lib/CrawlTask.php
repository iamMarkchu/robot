<?php
namespace Lib;

use Lib\CouponApi;
class CrawlTask
{
  protected $api;
  protected $taskID;
  protected $competitorID;
  protected $fileName;
  public function __construct($competitorID, $fileName)
  {
      $this->api = new CouponApi();
      $this->competitorID = $competitorID;
      $this->fileName = $fileName;
  }

  public function getTaskID()
  {
    return $this->taskID;
  }

  public function start()
  {
    $form_params = [
      'competitor_id' => $this->competitorID,
      'crawl_file' => '',
      'status' => 'pending',
      'start_at' => date('Y-m-d H:i:s'),
      'end_at' => date('Y-m-d H:i:s')
    ];
    $this->taskID = $this->api->request('/api/crawl-task/start', 'POST', $form_params);
  }

  public function finish()
  {
    $multipart = [
      [
        'name' => 'crawl_file',
        'contents' => fopen($this->fileName, 'r')
      ],
      [
        'name' => 'end_at',
        'contents' => date('Y-m-d H:i:s')
      ]
    ];
    return $this->api->request('/api/crawl-task/finish/'. $this->taskID, 'POST', $multipart, TRUE);
  }
}
