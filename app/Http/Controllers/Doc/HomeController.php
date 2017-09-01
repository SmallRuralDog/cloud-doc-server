<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/24
 * Time: 17:46
 */

namespace App\Http\Controllers\Doc;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use JWTAuth;

class HomeController extends Controller
{
    public function index()
    {
        return view('Doc.index');
    }


    public function home(){

        $user = auth()->user();
        $token = Cache::get('token', function () use ($user) {
            $token = JWTAuth::fromUser($user);
            Cache::put('token',$token,86400);
            return $token;
        });
        $res['token'] = $token;

        return view('Doc.vue-index',$res);
    }
}