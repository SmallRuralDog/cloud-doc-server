<?php


namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocClass;
use Illuminate\Http\Request;

class DocController extends Controller
{

    public function class_list()
    {
        $doc_class_list = DocClass::query()->where("parent_id", 1)->get(['id', 'title']);

        foreach ($doc_class_list as $v) {
            $v->son = $v->son()->get(['id', 'title', 'icon']);
        }
        return response()->json($doc_class_list);
    }


    public function doc_class_list(Request $request)
    {

        $class_id = $request->input("class_id");
        $doc = Doc::query()->where("state",">=", 0)->orderBy("order", "desc")->orderBy("id");
        $doc->where("doc_class_id", $class_id);
        $list = $doc->paginate(20, ['id', 'title', 'desc', 'cover','doc_class_id']);

        return response()->json($list);
    }
}