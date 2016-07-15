<?php

/**
* 	微信支付新推出的js sdk
*/
class JsSdk {

    private $appId;
    private $appSecret;
    
    /**
    * @param unknown $arr app_secret app_secret: 
    */
    public function __construct($arr) {
        $this->CI    =& get_instance();
        if (empty($arr)) {
            show_error("缺少必填参数");
        }
        $this->appId = $arr['app_id'];
        $this->appSecret = $arr['app_secret']; 
        $this->CI->load->driver('cache',array('adapter' => 'redis'));
    }
    
    public function getSignPackage() {
        $jsapiTicket = $this->getJsApiTicket();
        
        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        $timestamp = time();
        $nonceStr = $this->createNonceStr();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        
        $signature = sha1($string);
        
        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage; 
    }
    
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
    
    private function getJsApiTicket() {
        // jsapi_ticket 应该全局存储与更新
        $name = $this->CI->config->item('api_weixin')['paths']['ticket'];
        $data = json_decode($this->CI->cache->get($name));
        if (empty($data) || $data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            $ticket_params = array(
                'type' => 'jsapi',
                'access_token' => $accessToken
            );
            $tickets = sendCurl($this->CI->config->item('api_weixin')['urls']['ticket'], $ticket_params, 'get', False);
            $res = json_decode($tickets);
            $ticket = $res->ticket;
            if ($ticket) {
                $data = array();
                $data['expire_time'] = time() + 7000;
                $data['jsapi_ticket'] = $ticket;
                $this->CI->cache->save($name,json_encode($data),7000);
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }
        return $ticket;
    }
    
    private function getAccessToken() {
        // access_token 应该全局存储与更新
        $name = $this->CI->config->item('api_weixin')['paths']['token'];
        $data = json_decode($this->CI->cache->get($name));
        if (empty($data) || $data->expires_in < time()) {
            $token_params = array(
                'grant_type' => 'client_credential',
                'appid' => $this->appId,
                'secret' => $this->appSecret
            );
            $token = sendCurl($this->CI->config->item('api_weixin')['urls']['token'], $token_params, 'get', False);
            $token = json_decode($token);
            $access_token = $token->access_token;
            if ($access_token) {
                $data = array();
                $data['expires_in'] = time() + 7000;
                $data['access_token'] = $access_token;
                $this->CI->cache->save($name,json_encode($data),7000);
            }
        } else {
            $access_token = $data->access_token;
        }
        return $access_token;
    }
    
    private function httpGet($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $url);
        
        $res = curl_exec($curl);
        curl_close($curl);
        
        return $res;
    }
}
