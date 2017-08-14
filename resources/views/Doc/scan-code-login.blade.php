<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
    <link rel="shortcut icon" type="image/x-icon" href="">
    <title>云档登录</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <script src="https://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
    <style>
        .theme-login-layout{background:#eef0ed;}
        #theme-login-form{ width:800px; height: 600px; margin:0 auto 50px auto; background:#fff; overflow:hidden; clear:both; position: relative;}
        #theme-login-form .QRcode{
            position: absolute; top: 0; right: 0; width:93px; height: 93px;
            filter:alpha(opacity=50); -moz-opacity:0.5; -khtml-opacity: 0.5; opacity: 0.5;
        }
        #theme-login-form .QRcode:hover{
            filter:alpha(opacity=100); -moz-opacity:1; -khtml-opacity:1; opacity:1;
            cursor: pointer;
        }
        #theme-login-form .QRcode-layout{
             top: 0; right: 0;  bottom: 0; left: 0;
            width:100%; height:100%; background: #fff; z-index: 99999;
        }
        #theme-login-form .QRcode-layout .QRcode-header{ width: 500px; margin: 80px auto 50px auto; }
        #theme-login-form .QRcode-layout .QRcode-header b,#theme-login-form .QRcode-layout .QRcode-header span{ display: block; text-align: center;}
        #theme-login-form .QRcode-layout .QRcode-header b{ font-size: 24px; color: #015cc2; margin: 0 0 5px;}
        #theme-login-form .QRcode-layout .QRcode-header span{  font-size: 12px; color: #595959; }
        #theme-login-form .QRcode-layout .QRcode-content{ width: 300px; height: 300px; margin: 0 auto; }
        .theme-login-header{ height: 100px; }
        .theme-login-footer{ width:800px; margin: 0 auto; color: #666666;}
        .theme-login-footer,.login-footer dl{overflow: hidden; clear: both;}
        .theme-login-footer dl dt{ height: 30px; line-height: 30px; margin: 0 0 5px 0; color: #a8a8a8;}
        .theme-login-footer dl dt a{ margin: 0 5px; }
        .theme-login-footer dl dt a,.login-footer dl dt a:link,.login-footer dl dt a:visited,.login-footer dl dt a:hover,.login-footer dl dt a:active,.login-footer dl dt a:focus{ color: #666666;}

        .theme-login-footer dl dd{ margin:0 0 5px; font-size: 12px; text-align: center; clear:both; height:auto; overflow:hidden;}
    </style>
</head>
<body class="theme-login-layout">
<div class="theme-login-header"></div>
<div id="theme-login-form">
    <div class="QRcode"></div>
    <div class="QRcode-layout">
        <div class="QRcode-header">
            <b>小程序扫码登录</b>
            <span>请使用最新版云档小程序进行扫码登录</span>
        </div>
        <div class="QRcode-content">{!! QrCode::size(300)->generate(json_encode(['type'=>'login','key'=>$key])); !!}</div>
    </div>
</div>
<div class="theme-login-footer">
    <dl>
        <dd>&copy 2007 - <script>var year = new Date();document.write(year.getFullYear());</script> 广西老友粉网络科技有限责任公司</dd>
    </dl>
</div>

<script>
$(function () {
    function ck_login() {
        $.get("{{route('check_login')}}",{
            _token:"{{csrf_token()}}",
            key:"{{$key}}"
        },function(res){
            if(res.state){
                window.location.href = '/'
            }
        });
    }

    setInterval(ck_login,5000)
})
</script>
</body>
</html>