<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/15
 * Time: 11:01
 */

namespace App\Http\Controllers\Api\V3;


use App\Http\Controllers\Api\BaseController;
use App\Models\Question;
use App\Models\UploadTemp;
use Illuminate\Http\Request;

class QuestionController extends BaseController
{

    public function question_post(Request $request)
    {
        $user = $this->get_user();
        $parent_id = $request->input("parent_id");
        $res_id = $request->input("res_id");
        $title = $request->input("title");
        $desc = $request->input("desc");
        $source = $request->input("source");
        $source_id = $request->input("source_id");
        $pics = $request->input("pics");

        $img = [];
        if (is_array($pics)) {
            $img = UploadTemp::query()->whereIn('key', $pics)->orderBy('index')->get(['path'])->pluck('path');
        }

        $question = Question::query()->create([
            'user_id' => $user->id,
            'parent_id' => $parent_id,
            'res_id' => $res_id,
            'title' => $title,
            'desc' => $desc,
            'source' => $source,
            'source_id' => $source_id,
            'pics' => json_encode($img),
            'state'=>1
        ]);
        if ($question->id > 0) {
            UploadTemp::query()->whereIn('key', $pics)->delete();
        }

        return $this->api_return(200, '发布成功', $question);
    }


    public function upload_img(Request $request)
    {
        $user = $this->get_user();
        $data_id = $request->input("data_id");
        $index = $request->input("index");
        $name = $request->input("name");
        $file = $request->file($name);
        $res = $this->upload($file, "question");
        if ($res) {
            $upload = UploadTemp::query()->create([
                'user_id' => $user->id,
                'path' => $res,
                'data_id' => $data_id,
                'state' => 0,
                'index' => $index,
                'key' => md5($res)
            ]);
            return $this->api_return(200, '上传成功', $upload->key);
        } else {
            return $this->api_return(0, '上传失败');
        }
    }
}