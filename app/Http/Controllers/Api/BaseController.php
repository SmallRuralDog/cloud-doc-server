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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class BaseController extends Controller
{
    use Helpers, ValidatesRequests;

    protected function get_user()
    {
        try {
            return JWTAuth::parseToken()->authenticate();
        } catch (\Exception $exception) {
            return false;
        }

    }

    protected function api_return($status_code, $message = '', $data = [])
    {
        return response()->json(['status_code' => $status_code, 'message' => $message, 'data' => $data]);
    }

    /**
     * @param $file
     * @param $path
     * @return bool
     */
    protected function upload($file, $path)
    {
        if ($file->isValid()) {
            $disk = Storage::disk('qiniu');
            $result = $disk->put($path, $file);
            if (!$result) {
                return false;
            } else {
                return $result;
            }
        } else {
            return false;
        }
    }
}