<?php

class Order extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        
    }
    
    public function wxPay() {
        
	$openid = "";//openid   
        $goods_name ="";//商品支付描述
        $total_pay = "";//支付金额
        $ordersn = "";//订单编号
        $depict = "";//商品详情描述
        $wx_pay = $this->pay->setPay($openid,$goods_name,$total_pay,$ordersn,$depict);
        echo json_encode(array('signPackage'=>$wx_pay['signPackage'],'payPackage'=>$wx_pay['payPackage'],'orderid'=>$orderinfo['ordersn']));
    }
}
