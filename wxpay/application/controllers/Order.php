<?php

class Order extends CI_Controller
{
    public function __construct() {
        parent::__construct();
        
    }
    
    public function wxPay() {
        
        $openid = "";
        $goods_name ="";
        $total_pay = "";
        $ordersn = "";
        $depict = "";
        $wx_pay = $this->pay->setPay($openid,$goods_name,$total_pay,$ordersn,$depict);
        echo json_encode(array('signPackage'=>$wx_pay['signPackage'],'payPackage'=>$wx_pay['payPackage'],'orderid'=>$orderinfo['ordersn']));
    }
}