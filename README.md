# 云档小程序服务端代码
## 运行环境 
- PHP 7.1 或更高    低版本在小程序登陆的时候报错mcrypt_module_open
- nginx  
- mysql 5.6 或更高


>建议用`宝塔`一键搭建环境，还可以一键生成https证书  [http://bt.cn](http://bt.cn ) 服务器最好用`linux` `contos 7`

## 安装教程
- 创建一个空数据库 将数据库文件导入数据库 数据库文件在根目录 `cloud_doc_server.zip`

- 修改根目录`config`文件夹下面的 `database.php`
```
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', ''),//数据库地址
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', ''),//数据库名称
    'username' => env('DB_USERNAME', ''),//数据库用户名
    'password' => env('DB_PASSWORD', ''),//数据库密码
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => true,
    'engine' => null,
],
```
- 去七牛申请一个账号，创建空间 修改根目录`config`文件夹下面的 `filesystems.php`
```
'qiniu' => [
    'driver'  => 'qiniu',
    'domains' => [
        'default'   => 'http://cloud-doc-img.leyix.com', //你的七牛域名
        'https'     => '',         //你的HTTPS域名
        'custom'    => 'http://cloud-doc-img.leyix.com',                //你的自定义域名
    ],
    'access_key'=> '',  //AccessKey
    'secret_key'=> '',  //SecretKey
    'bucket'    => 'cloud-doc',  //Bucket名字
    'notify_url'=> '',  //持久化处理回调地址
    'access'    => 'public'  //空间访问控制 public 或 private
]
```
如果用本地存储图片，这里只要配置域名
- 修改根目录`config`文件夹下面的 `app.php`
```
'url' => env('APP_URL', 'https://cloud-doc.leyix.com'),//改成你的域名
```
- 修改根目录`config`文件夹下面的 `admin.php`
```
 'prefix'    => 'admin',//后台地址前缀 这里是什么  后台地址就是 www.xxxxx.com/你设置的后缀
 
 'upload'  => [
    'disk' => 'qiniu',//本地换成public
    'directory'  => [
        'image'  => 'image',
        'file'   => 'file',
    ],
    'host' => 'http://cloud-doc-img.leyix.com/',//这里改成你的七牛域名
],
```

- 修改根目录`config`文件夹下面的 `wx.php`
```
'xcx_AppID'=>'',//小程序AppID
'xcx_AppSecret'=>'',//小程序AppSecret
```


## 运行前设置


- 代码上传服务器
- 根目录 storage 目录可写权限
- 域名解析到 public目录 并配好laravel伪静态
- 如果能访问后台那么就说明安装成功  默认的账号和密码 都是 `admin`
```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## 其他说明
系统使用`laravel5.4`开发，后台使用[laravel-admin](https://github.com/z-song/laravel-admin)，接口与权限认证使用`dingo api+jwt`

### 用到了一些第三方库
```
"dingo/api": "1.0.x@dev",
"encore/laravel-admin": "1.4.x-dev",
"guzzlehttp/guzzle": "^6.2",
"jaeger/querylist": "^3.2",
"laravel/framework": "5.4.*",
"laravel/tinker": "~1.0",
"league/html-to-markdown": "^4.4",
"overtrue/laravel-follow": "^1.1",
"overtrue/wechat": "~3.1",
"simplesoftwareio/simple-qrcode": "~1",
"tymon/jwt-auth": "0.5.*",
"zgldh/qiniu-laravel-storage": "^0.6.7"
```
