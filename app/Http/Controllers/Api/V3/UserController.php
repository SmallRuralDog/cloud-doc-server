<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/10
 * Time: 9:40
 */

namespace App\Http\Controllers\Api\V3;


use App\Extend\WxApp\WXBizDataCrypt;
use App\Http\Controllers\Api\BaseController;
use App\Models\WxUser;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends BaseController
{


    public function login(Request $request)
    {

        error_reporting(0);
        $code = $request->input("code");
        $encryptedData = $request->input("encryptedData");
        $iv = $request->input("iv");
        if (empty($code) || empty($encryptedData) || empty($iv)) {
            return $this->response->array(['states_code' => 0, 'message' => '登录失败']);
        }
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . config('wx.xcx_AppID') . "&secret=" . config('wx.xcx_AppSecret') . "&js_code=" . $code . "&grant_type=authorization_code";
        $http = new \GuzzleHttp\Client();
        $res = $http->get($url);
        $re_data = json_decode($res->getBody(), true);
        $pc = new WXBizDataCrypt(config('wx.xcx_AppID'), $re_data['session_key']);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);
        if ($errCode == 0) {
            $data = json_decode($data);
            $wx_user = WxUser::query()->updateOrCreate(['open_id' => $data->openId], [
                'open_id' => $data->openId,
                'nick_name' => $data->nickName,
                'avatar_url' => $data->avatarUrl,
                'city' => $data->city,
                'country' => $data->country,
                'gender' => $data->gender,
                'language' => $data->language,
                'province' => $data->province,
                'app_id' => config('wx.xcx_AppID'),
            ]);
            if ($wx_user->user_id <= 0) {
                $user = User::query()->firstOrCreate(['wx_user_id' => $wx_user->id], [
                    'name' => $data->nickName,
                    'email' => md5($data->openId) . "@cloud-doc.com",
                    'password' => bcrypt($data->openId),
                    'is_auth' => 0,
                    'wx_user_id' => $wx_user->id,
                    'avatar' => $wx_user->avatar_url,
                ]);
                if ($user->id <= 0) {
                    return $this->response->array(['states_code' => $errCode, 'message' => '用户创建失败']);
                } else {
                    $wx_user->user_id = $user->id;
                    $wx_user->save();
                }
            } else {
                $user = User::query()->findOrFail($wx_user->user_id);
            }

            $token = JWTAuth::fromUser($user);

            $user['token'] = $token;
            $user['ttl'] = config('jwt.ttl');

            return $this->response->array(['states_code' => 200, 'message' => '登录成功', 'data' => $user]);

        } else {
            return $this->response->array(['states_code' => $errCode, 'message' => '登录失败']);
        }


    }

}