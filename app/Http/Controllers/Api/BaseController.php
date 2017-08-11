<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/10
 * Time: 9:39
 */

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BaseController extends Controller
{
    use Helpers, ValidatesRequests;

    protected function get_user()
    {
        return JWTAuth::parseToken()->authenticate();
    }

    protected function api_return($status_code, $message = '', $data = [])
    {
        return response()->json(['status_code' => $status_code, 'message' => $message, 'data' => $data]);
    }
}