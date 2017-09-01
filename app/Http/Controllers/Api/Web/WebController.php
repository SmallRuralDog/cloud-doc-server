<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/31
 * Time: 15:33
 */

namespace App\Http\Controllers\Api\Web;


use App\Http\Controllers\Api\BaseController;
use App\Models\Tag;
use Illuminate\Http\Request;

class WebController extends BaseController
{

    public function search_tag(Request $request){
        $user = $this->get_user();
        $key = $request->input("key");
        $list = Tag::query()->whereIn('user_id',[0,$user->id])->where('name','like',"%{$key}%")->get(['id','name']);
        return $this->api_return(200,'',$list);
    }

}