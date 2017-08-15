<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/15
 * Time: 11:01
 */

namespace App\Http\Controllers\Api\V3;


use App\Http\Controllers\Api\BaseController;
use App\Models\UploadTemp;
use Illuminate\Http\Request;

class QuestionController extends BaseController
{

    public function quiz(Request $request)
    {
        $user = $this->get_user();
        $title = $request->input("title");
        $desc = $request->input("desc");
        $source = $request->input("source");
        $source_id = $request->input("source_id");
    }


    public function upload_img(Request $request)
    {
        $user = $this->get_user();
        $data_id = $request->input("data_id");
        $name = $request->input("name");
        $file = $request->file($name);
        $res = $this->upload($file, "question");
        if ($res) {
            $upload = UploadTemp::query()->create([
                'user_id' => $user->id,
                'path' => $res,
                'data_id' => $data_id,
                'state' => 0
            ]);
            $this->api_return(200, '上传成功', $upload);
        } else {
            $this->api_return(0, '上传失败');
        }
    }
}