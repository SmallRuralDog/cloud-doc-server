<?php

namespace App\Http\Controllers\Api;

use App\Extend\Parsedown;
use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocMenu;
use App\Models\DocPage;
use Illuminate\Http\Request;

/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/19
 * Time: 18:43
 */
class DocController extends Controller
{

    public function lists()
    {
        $doc = Doc::query()->where("state",1);
        $list = $doc->paginate(50, ['id', 'title', 'desc', 'cover']);
        return response()->json($list);
    }

    public function info(Request $request)
    {
        $doc_id = $request->input("doc_id");
        $doc = Doc::query()->find($doc_id);
        $doc->doc_class;
        return response()->json(['data' => $doc, 'message' => '', 'status_code' => 1]);
    }

    public function menu(Request $request){
        $doc_id = $request->input("doc_id");
        $doc_menu = DocMenu::query()->where("doc_id",$doc_id);
        $list = $doc_menu->get(['id','title']);
        foreach ($list as $k=>$v){
            $list[$k]->page = $v->doc_page()->orderBy("order","asc")->orderBy("id","asc")->get(['id','menu_title','menu_id']);
        }
        return response()->json(['data' => $list, 'message' => '', 'status_code' => 1]);
    }

    public function page(Request $request){
        $page_id = $request->input("page_id");

        $page = DocPage::query()->find($page_id,['content','updated_at']);
        //$Parsedown = new Parsedown();
        //$page->content = $Parsedown->text($page->content);

        return response()->json(['data' => $page, 'message' => '', 'status_code' => 1]);
    }
}