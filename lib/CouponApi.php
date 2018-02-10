<?php
namespace Lib;
use GuzzleHttp\Client;
use Redis;
class CouponApi
{
    protected $client;
    protected $redis;
    protected $tokenKey = 'coupon:accessToken';
    protected $requestForm = [
        'grant_type' => 'password',
        'client_id' => '1',
        'client_secret' => 'Xv0xlFFG7ln4qgMFl5kLZlKzPLVcDQtfMFTupq9W',
        'username' => 'trantow.eunice@example.com',
        'password' => 'secret',
        'scope' => '',
    ];
    protected $apiRoot = 'http://xplan.mark';
    protected $tokenUrl = '/oauth/token';
    protected $token = '';
    protected $headerWithToken = '';
    public function __construct()
    {
        $this->client = new Client(['base_uri' => $this->apiRoot]);
        $this->redis = new Redis;
        $this->redis->connect('127.0.0.1', 6379);
        $this->token = $this->getTokenByPassword();
        $this->headerWithToken = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->token,
        ];
    }

    public function getTokenByPassword()
    {
        $accessToken = $this->redis->get($this->tokenKey);
        if(!empty($accessToken))
        {
            return $accessToken;
        }else{
            return $this->requestToken();
        }
    }

    public function requestToken()
    {
        $response = $this->client->post($this->tokenUrl, [ 'form_params' => $this->requestForm ]);
        if($response->getStatusCode() == 200)
        {
            $token = json_decode($response->getBody(), TRUE);
            $this->redis->set($this->tokenKey, $token['access_token'], ['nx', 'ex' => intval($token['expires_in'])]);
            return $token['access_token'];
        }else{
            die('access_token can not get now !');
        }
    }

    public function request($url, $requestType='GET', $data = [], $includeFile=FALSE)
    {
        $this->token = $this->getTokenByPassword();
        $options = [
            'headers' => $this->headerWithToken
        ];
        $type = 'form_params';
        if($includeFile) $type = 'multipart';
        if(!empty($data))   $options[$type] = $data;

        $response = $this->client->request($requestType, $url, $options);
        if($response->getStatusCode() == 200)
        {
            return json_decode($response->getBody(), TRUE);
        }else{
            echo 'wrong';
        }
    }
}
