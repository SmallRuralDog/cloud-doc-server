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
        .text-center {
            text-align: center;
            padding-top: 200px;
        }
    </style>
</head>
<body>
<div class="visible-print text-center">
    {!! QrCode::size(300)->generate(json_encode(['type'=>'login','key'=>$key])); !!}
    <p>打开“云档”微信小程序，进入个人中心扫码登录</p>
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