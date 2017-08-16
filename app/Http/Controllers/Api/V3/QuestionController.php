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
use App\Models\QuestionReply;
use App\Models\UploadTemp;
use Carbon\Carbon;
use Illuminate\Http\Request;

class QuestionController extends BaseController
{

    public function index()
    {
        $question = Question::query()->where('state', 1);


        $question->orderBy('created_at', 'desc');

        $page = $question->paginate(20, ['id', 'user_id', 'title', 'pics', 'created_at', 'view_count']);

        foreach ($page as $v) {
            $v->user = $v->user()->first(['id', 'name','title', 'avatar']);

            $v->created = Carbon::parse($v->created_at)->diffForHumans();
            $v->pics_arr = $v->pics_arr;
            $v->pics_type = count($v->pics_arr) % 3 == 0 ? 3 : count($v->pics_arr) % 3;
        }

        return $page;
    }

    public function page(Request $request)
    {
        $id = $request->input("id");
        $page = $request->input("page",1);

        $v = Question::query()->find($id,['id', 'user_id', 'title','desc', 'pics', 'created_at', 'view_count']);
        $v->user = $v->user()->first(['id', 'name','title', 'avatar']);

        $v->created = Carbon::parse($v->created_at)->diffForHumans();
        $v->pics_arr = $v->pics_arr;
        $v->pics_type = count($v->pics_arr) % 3 == 0 ? 3 : count($v->pics_arr) % 3;


        $reply = QuestionReply::query()->where('question_id',$v->id)->where('state',1);
        $list = $reply->paginate(10);
        foreach ($list as $item) {
            $item->user = $item->user()->first(['id', 'name', 'title','avatar']);
        }

        if ($page == 1 ) {
            $json = json_encode($list);

            $list = json_decode($json);

            $list->wenda = $v;
        }


        return response()->json($list);
    }

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
            $img = UploadTemp::query()->whereIn('key', $pics)->orderBy('index')->limit(9)->get(['path'])->pluck('path');
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
            'state' => 1
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