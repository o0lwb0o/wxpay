<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>微信支付</title>
        <script type="text/javascript" src='<?php echo base_url()?>assets/js/jQuery 3.0.0.js' charset='utf-8'></script>
    </head>
    <style type="text/css">
        body{text-align:center;} 
        .button{background: #222 url(overlay.png) repeat-x;display: inline-block;padding: 5px 10px 6px;color: #fff;text-decoration: none;-moz-border-radius: 6px;-webkit-border-radius: 6px;-moz-box-shadow: 0 1px 3px rgba(0,0,0,0.6);-webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.6);text-shadow: 0 -1px 1px rgba(0,0,0,0.25);border-bottom: 1px solid rgba(0,0,0,0.25);position: relative;cursor: pointer}
    </style>
    <body>
        <div>
            <h1>一分钱的幻想</h1>
        </div>
        <div>
            <a id="sub" class="button">支付</a>
        </div>
    </body>
    <script>
    //点击确认支付按钮
	$('#sub').bind('click', function() {
		//创建订单，向微信支付发起请求，返回支付所需参数
    	$.ajax({
            type: "POST",
            url: '<?php echo site_url('Order/wxPay') ?>',
           // data: info,
            async: false,
            dataType: "json",
            success: function(data) {
                
                    //微信支付所需参数
                    var signPackage = data.signPackage;
                    var payPackage = data.payPackage;
                    var orderid = data.orderid;
                    
                	wx.config({
                        appId: signPackage.appId,
                        timestamp: signPackage.timestamp,
                        nonceStr: signPackage.nonceStr,
                        signature: signPackage.signature,
                        jsApiList: [
                			'chooseWXPay'
                		]
                	});
                	
                	wx.ready(function () {
                        wx.chooseWXPay({
                	        timestamp: payPackage.timeStamp,
                	        nonceStr: payPackage.nonceStr,
                	        package: payPackage.package,
                	        signType: payPackage.signType,
                	        paySign: payPackage.paySign,
                	        success: function (res) {
                    	        //支付成功跳转
                	        	window.location.href='<?php echo site_url("buy/wxSuccess");?>' + '/' + orderid;
                	        },
                	        fail: function (res) {
                    	        //支付失败跳转
                	        	window.location.href='<?php echo site_url("buy/wxFail");?>' + '/' + orderid;
                	        }
                        });
                    });
            },
            error: function() {
                alert("网络错误，稍后请重试！");
            },
        });
    	});
    </script>
</html>