<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/15
 * Time: 11:01
 */

namespace App\Http\Controllers\Api\V3;


use App\Http\Controllers\Api\BaseController;
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

}