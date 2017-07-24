<?php


namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocClass;
use App\Models\DocPage;
use Illuminate\Http\Request;

class DocController extends Controller
{

    public function index(){
        $doc = Doc::query();
        $doc->where("state", "=", 1);
        $doc->orderBy("order", "desc")->orderBy("id");
        $list = $doc->paginate(5, ['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        foreach ($list as $k => $v) {
            $list[$k]->view_count = $v->doc_page()->sum("view_count");
        }
        return response()->json($list);
    }

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
        $doc = Doc::query();
        $doc->where("state", ">=", 0);
        $doc->orderBy("order", "desc")->orderBy("id");
        $doc->where("doc_class_id", $class_id);
        $list = $doc->paginate(5, ['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        foreach ($list as $k => $v) {
            $list[$k]->view_count = $v->doc_page()->sum("view_count");
        }

        return response()->json($list);
    }

    public function doc_page(Request $request)
    {
        $doc_id = $request->input("doc_id");
        $page = DocPage::query()->where("doc_id", $doc_id)
            ->where("parent_id", 0)
            ->orderBy("order", "desc")
            ->select([
                'id',
                'title',
                'menu_title',
                'order',
                'parent_id'
            ])->get();
        return response()->json(['data' => $page, 'message' => '', 'status_code' => 1]);
    }

    public function page(Request $request)
    {
        $page_id = $request->input("page_id");

        $page = DocPage::query()->find($page_id, ['content', 'updated_at']);

        $page->increment("view_count");

        return response()->json(['data' => $page, 'message' => '', 'status_code' => 1]);
    }

    public function get_my_doc(Request $request){
        $ids = $request->input("ids");

        $doc =Doc::query()->whereIn("id",$ids)->where("state",1)->get(['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        return response()->json($doc);
    }
}