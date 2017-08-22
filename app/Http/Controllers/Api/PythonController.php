<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/22
 * Time: 10:22
 */

namespace App\Http\Controllers\Api;


use App\Models\DocPage;
use Illuminate\Http\Request;

class PythonController extends BaseController
{
    public function collect(Request $request)
    {
        $id = $request->input('id');
        $content = $request->input("content");
        if (empty($id) || empty($content)) {
            return "数据错误";
        }


        $content = str_replace("(//", "(http://", $content);

        $page = DocPage::query()->findOrFail($id);
        $page->content = $content;
        $page->collect_state = 1;
        $page->state = 1;
        $page->save();
        return "采集成功";
    }

}