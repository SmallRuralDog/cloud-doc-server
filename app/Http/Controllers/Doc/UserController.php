<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/11
 * Time: 17:44
 */

namespace App\Http\Controllers\Doc;


use App\Http\Controllers\Controller;
use App\Models\ScanCode;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function login()
    {
        $scan_code = ScanCode::query()->create([
            'key' => str_random(32),
            'user_id' => 0,
            'add_time' => time()
        ]);
        return view('Doc.scan-code-login', $scan_code);
    }

    public function check_login(Request $request)
    {
        $key = $request->input("key");

        $info = ScanCode::query()->where('key', $key)->first();
        if ($info->user_id > 0 && $info->add_time > time() - 5 * 60) {
            $user = User::query()->findOrFail($info->user_id);
            Auth::login($user);
            return response()->json(['state' => true]);
        } else {
            return response()->json(['state' => false]);
        }
    }

}