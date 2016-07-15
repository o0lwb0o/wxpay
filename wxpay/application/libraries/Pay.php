<?php

/**
 * 微信支付
 * @author liuwb
 *
 */
class Pay{
    
    public function __construct(){
        $this->CI    =& get_instance();
        $arr = array();
        $arr['app_id'] = $this->CI->config->item('api_weixin')['app_id'];//公众账号ID
        $arr['app_secret'] = $this->CI->config->item('api_weixin')['app_secret'];
        $this->CI->load->library('weixin/JsSdk',$arr,'jssdk');
        $this->CI->load->library('weixin/CommonUtilPub');
        $this->CI->load->library('weixin/WxpayClientPub');
        $this->CI->load->library('weixin/UnifiedOrderPub','','unifiedOrder');
    }
    
    
    /**
     * 统一下单
     * @param unknown $openId 用户openId
     * @param unknown $depict 商品描述
     * @param unknown $money 总金额,单位为分
     * @param unknown $orderid 商户订单号
     * @param string $detail 商品详情
     * 
     * @return array
     */
    public function setPay($openId,$depict,$money,$orderid,$goodsid,$detail='')
    {
        if (empty($openId) || empty($money) || empty($orderid)) {
            show_error("缺少必填参数！","500","出错了");
        }
        // 获取jssdk相关参数
        $signPackage = $this->CI->jssdk->GetSignPackage();
        $timeStamp = $signPackage['timestamp'];
        $nonceStr = $signPackage['nonceStr'];
        // 获取prepay_id
        $this->CI->unifiedOrder->setParameter("openid",$openId);//用户openId
        $this->CI->unifiedOrder->setParameter("body", $depict);//商品描述，不要超过32个字符
        $this->CI->unifiedOrder->setParameter("out_trade_no", $orderid);//商户订单号
        $this->CI->unifiedOrder->setParameter("total_fee", $money);//总金额,单位为分
        $this->CI->unifiedOrder->setParameter("notify_url",$this->CI->config->item('api_weixin')['urls']['callback']);//通知地址
        $this->CI->unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
        $this->CI->unifiedOrder->setParameter("nonce_str", $nonceStr);//随机字符串
        $startTime = date('YmdHis');
        $expireTime = date("YmdHis",strtotime("+1 day"));
        $this->CI->unifiedOrder->setParameter("time_start",$startTime);//交易起始时间
        $this->CI->unifiedOrder->setParameter("time_expire",$expireTime);//交易结束时间 
        if (!empty($detail)) {
            $this->CI->unifiedOrder->setParameter("detail",$detail);//商品详情
        }
        $prepayId = $this->CI->unifiedOrder->getPrepayId();
        
        // 计算paySign
        $payPackage = [
            "appId" => $this->CI->config->item('api_weixin')['app_id'],
            "nonceStr" => $nonceStr,
            "package" => "prepay_id=" . $prepayId,
            "signType" => "MD5",
            "timeStamp" => $timeStamp
        ];
        $paySign = $this->CI->unifiedOrder->getSign($payPackage);
        $payPackage['paySign'] = $paySign;
        $arr = array();
        $arr['signPackage'] = $signPackage;
        $arr['payPackage'] = $payPackage;
        write_log('wxpay', "value | ".json_encode($arr));
        return $arr;
    }
}