<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/29
 * Time: 16:12
 */

namespace App\Http\Controllers\Api\V3;


use App\Http\Controllers\Api\BaseController;
use EasyWeChat\Foundation\Application;

class DocPlayController extends BaseController
{

    protected $payment;

    public function __construct()
    {
        $options = [
            // 前面的appid什么的也得保留哦
            'app_id' => config('wx.xcx_AppID'),
            // ...
            // payment
            'payment' => [
                'merchant_id' => 'your-mch-id',
                'key' => 'key-for-signature',
                'cert_path' => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
                'key_path' => 'path/to/your/key',      // XXX: 绝对路径！！！！
                'notify_url' => '默认的订单回调地址',       // 你也可以在下单时单独设置来想覆盖它
                // 'device_info'     => '013467007045764',
                // 'sub_app_id'      => '',
                // 'sub_merchant_id' => '',
                // ...
            ],
        ];
        $app = new Application($options);
        $this->payment = $app->payment;
    }


}