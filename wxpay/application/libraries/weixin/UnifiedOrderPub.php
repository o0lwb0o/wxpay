<?php

/**
 * 统一支付接口类
 * @author liuwb
 *
 */
class UnifiedOrderPub extends WxpayClientPub
{	
	public function __construct() 
	{
	    $this->CI    =& get_instance();
		//设置接口链接
		$this->url = $this->CI->config->item('api_weixin')['urls']['wxpay'];
		//设置curl超时时间
		$this->curl_timeout = $this->CI->config->item('api_weixin')['curl_timeout'];
	}
	
	/**
	 * 生成接口参数xml
	 */
	public function createXml()
	{

			//检测必填参数
			if($this->parameters["out_trade_no"] == null) {
				show_error("缺少必填参数out_trade_no！","500","出错了");
			} elseif($this->parameters["body"] == null){
				show_error("缺少必填参数body！","500","出错了");
			} elseif ($this->parameters["total_fee"] == null ) {
				show_error("缺少必填参数total_fee！","500","出错了");
			} elseif ($this->parameters["notify_url"] == null) {
				show_error("缺少必填参数notify_url！","500","出错了");
			} elseif ($this->parameters["trade_type"] == null) {
				show_error("缺少必填参数trade_type！","500","出错了");
			} elseif ($this->parameters["trade_type"] == "JSAPI" && $this->parameters["openid"] == NULL) {
				show_error("缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！","500","出错了");
			}
		   	$this->parameters["appid"] = $this->CI->config->item('api_weixin')['app_id'];//公众账号ID
		   	$this->parameters["mch_id"] = $this->CI->config->item('api_weixin')['mch_id'];//商户号
		   	$this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip	    
            if (!isset($this->parameters["nonce_str"])) {
              $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            }
		    $this->parameters["sign"] = $this->getSign($this->parameters);//签名
		    return  $this->arrayToXml($this->parameters);

	}
	
	/**
	 * 获取prepay_id
	 */
	public function getPrepayId()
	{
		$this->postXml();
		$this->result = $this->xmlToArray($this->response);
        write_log('wxpay', "signature | ".json_encode($this->result));
		$prepay_id = $this->result["prepay_id"];
		return $prepay_id;
	}
	
}
