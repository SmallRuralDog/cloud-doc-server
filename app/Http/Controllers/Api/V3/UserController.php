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
use App\Models\Doc;
use App\Models\DocPage;
use App\Models\Question;
use App\Models\QuestionReply;
use App\Models\ScanCode;
use App\Models\UserFollow;
use App\Models\WxUser;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends BaseController
{

    public function index()
    {
        $user = $this->get_user();
        $wx_user = $user->wx_user()->first(['user_id', 'nick_name', 'avatar_url', 'city', 'country', 'gender', 'language', 'province']);

        $user_doc = UserFollow::query()->where('user_id', $user->id)->where('type', 'doc')
            ->orderBy('updated_at', 'desc')->get(['data_id']);

        foreach ($user_doc as $k => $v) {
            $user_doc[$k] = $v->doc()->first(['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);
        }

        $re['user'] = $wx_user;
        $re['user_data'] = [
            'follow' => 0,
            'fans' => 0,
            'scan_code_title' => '扫一扫，登录网页版创建文档',
            'doc' => $user_doc,
            'doc_page'=>$this->user_doc_page_collect()
        ];

        return $this->response->array(['status_code' => 200, 'message' => '', 'data' => $re]);
    }

    public function user_like(Request $request)
    {
        $user = $this->get_user();
        $data_id = $request->input("key");
        $type = $request->input("type");
        if ($data_id <= 0 || !in_array($type, ['wenda', 'wenda-page', 'doc', 'doc-page'])) {
            return $this->api_return(0, '操作失败');
        }
        $res = [];
        switch ($type) {
            case 'wenda':
                $question = Question::query()->find($data_id);
                $res = $user->like($question);
                break;
            case 'wenda-page':
                $question_page = QuestionReply::query()->find($data_id);
                $res = $user->like($question_page);
                break;
            case 'doc':
                $tag = Doc::query()->find($data_id);
                $res = $user->like($tag);
                break;
            case 'doc-page':
                $tag = DocPage::query()->find($data_id);
                $res = $user->like($tag);
                break;
        }
        return $this->api_return(200, 'success', $res);
    }

    public function user_follow_cancel(Request $request)
    {
        $user = $this->get_user();
        $data_id = $request->input("key");
        $type = $request->input("type");
        if ($data_id <= 0 || !in_array($type, ['doc', 'doc_page', 'article', 'user'])) {
            return $this->api_return(0, '数据异常');
        }

        $uf = UserFollow::query()->where([
            'user_id' => $user->id,
            'data_id' => $data_id,
            'type' => $type
        ])->first();
        if (empty($uf)) {
            return $this->api_return(0, '数据异常');
        } else {
            if ($uf->delete()) {
                return $this->api_return(200, '取消成功');
            } else {
                return $this->api_return(0, '取消失败');
            }
        }
    }

    public function user_follow(Request $request)
    {
        $user = $this->get_user();
        $data_id = $request->input("key");
        $type = $request->input("type");
        if ($data_id <= 0 || !in_array($type, ['doc', 'doc_page', 'article', 'user'])) {
            return $this->api_return(0, '数据异常');
        }

        $uf = UserFollow::query()->updateOrCreate([
            'user_id' => $user->id,
            'data_id' => $data_id,
            'type' => $type
        ], [
            'user_id' => $user->id,
            'data_id' => $data_id,
            'type' => $type
        ]);

        return $this->api_return(200, '操作成功', $uf);

    }


    private function user_doc_page_collect()
    {
        $user = $this->get_user();
        $list = $user->likes(DocPage::class)->orderBy("followables.created_at", "desc")->get(['id','doc_id','title'])->toArray();
        foreach ($list as $k=>$v){
            unset($v['children']);
            $v['doc'] = Doc::query()->find($v['doc_id'],['id','title','desc','cover','h_cover']);
            $list[$k] = $v;
        }
        return $list;
    }

    public function scan_login(Request $request)
    {
        $key = $request->input("key");
        $user = $this->get_user();
        $info = ScanCode::query()->where('key', $key)->first();
        if ($info->user_id <= 0 && $info->add_time > time() - 5 * 60) {
            $info->user_id = $user->id;
            if ($info->save()) {
                return $this->api_return(200, '登录成功');
            } else {
                return $this->api_return(0, '登录失败');
            }
        } else {
            return $this->api_return(0, '二维码已过期');
        }
    }

    public function login(Request $request)
    {

        error_reporting(0);
        $code = $request->input("code");
        $encryptedData = $request->input("encryptedData");
        $iv = $request->input("iv");
        if (empty($code) || empty($encryptedData) || empty($iv)) {
            return $this->response->array(['status_code' => 0, 'message' => '登录失败']);
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
                    return $this->response->array(['status_code' => $errCode, 'message' => '用户创建失败']);
                } else {
                    $wx_user->user_id = $user->id;
                    $wx_user->save();
                }
            } else {
                $user = User::query()->findOrFail($wx_user->user_id);
            }

            $token = JWTAuth::fromUser($user);

            $wxuser = $user->wx_user()->first(['nick_name', 'avatar_url', 'city', 'country', 'gender', 'language', 'province']);

            $wxuser['token'] = $token;
            $wxuser['ttl'] = config('jwt.ttl');

            return $this->response->array(['status_code' => 200, 'message' => '登录成功', 'data' => $wxuser]);

        } else {
            return $this->response->array(['status_code' => $errCode, 'message' => '登录失败']);
        }


    }

}