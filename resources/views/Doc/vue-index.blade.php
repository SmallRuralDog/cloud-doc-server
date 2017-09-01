<!DOCTYPE html>
<html lang="zh-CN">

<head>
    <title>云档-在线文档创作平台</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{{asset('dist/main.43adc1b5648c75dbdba8.css')}}">
    <script>
        var HOST = '{{config('app.url')}}',TOKEN = '{{$token}}',USER = {nickname: '{{auth()->user()->name}}', avatar: '{{auth()->user()->avatar}}'};
    </script>
</head>

<body>
<div id="app"></div>
<script type="text/javascript" src="{{asset('/dist/vendors.43adc1b5648c75dbdba8.js')}}"></script>
<script type="text/javascript" src="{{asset('/dist/main.43adc1b5648c75dbdba8.js')}}"></script>
</body>
</html>
