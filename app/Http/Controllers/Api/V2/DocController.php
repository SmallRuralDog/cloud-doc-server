<?php


namespace App\Http\Controllers\Api\V2;


use App\Extend\OpenSearch\CloudsearchClient;
use App\Extend\OpenSearch\CloudsearchSearch;
use App\Extend\OpenSearch\CloudsearchSuggest;
use App\Extend\Thumb;
use App\Http\Controllers\Controller;
use App\Models\Doc;
use App\Models\DocClass;
use App\Models\DocPage;
use App\Models\DocSearch;
use Illuminate\Http\Request;

class DocController extends Controller
{

    public function index()
    {
        $doc = Doc::query();
        $doc->where("state", "=", 1)->where("is_hot", 1);
        $doc->orderBy("order", "desc")->orderBy("id");
        $list = $doc->paginate(20, ['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        foreach ($list as $k => $v) {
            $list[$k]->view_count = $v->doc_page()->sum("view_count");
        }
        return response()->json($list);
    }

    public function class_list()
    {
        $doc_class_list = DocClass::query()
            ->where("state", 1)
            ->where("parent_id", 1)->get(['id', 'title']);

        foreach ($doc_class_list as $v) {
            $v->son = $v->son()->where("state", 1)->get(['id', 'title', 'icon']);
        }
        return response()->json($doc_class_list);
    }


    public function doc_class_list(Request $request)
    {

        $class_id = $request->input("class_id");
        $doc = Doc::query();
        $doc->where("state", "=", 1);
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

        $page = DocPage::query()->find($page_id, ['id','doc_id','title','content', 'updated_at']);
        $page->doc_title = $page->doc()->first()->title;
        $page->increment("view_count");

        $page->content = str_replace("\n\n```","\n```",$page->content);

        return response()->json(['data' => $page, 'message' => '', 'status_code' => 1]);
    }

    public function get_my_doc(Request $request)
    {
        $ids = $request->input("ids");

        $doc = Doc::query()->whereIn("id", $ids)->where("state", 1)->get(['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        return response()->json($doc);
    }

    public function search_index(){
        $data =  DocSearch::query()->select([
            'name',
            \DB::raw("count(*) as num")
        ])->groupBy("name")->orderBy("num","desc")->limit(20)->get();

        return response()->json($data);
    }

    public function search(Request $request)
    {

        $key = $request->input("key");
        $page = $request->input("page");

        $rows = 20;

        $start = ($page - 1) * 20;


        $access_key = "";
        $secret = "";
        $host = "http://opensearch-cn-hangzhou.aliyuncs.com";//根据自己的应用区域选择API
        $key_type = "aliyun";  //固定值，不必修改
        $opts = array('host' => $host);
        $app_name = "cloud_doc";
        $client = new CloudsearchClient($access_key, $secret, $opts, $key_type);

        // 实例化一个搜索类
        $search_obj = new CloudsearchSearch($client);
        // 指定一个应用用于搜索
        $search_obj->addIndex($app_name);
        // 指定搜索关键词
        $search_obj->setQueryString("content:" . $key);
        // 指定返回的搜索结果的格式为json
        $search_obj->setFormat("json");


        $search_obj->setStartHit($start);

        $search_obj->setHits($rows);

        // 执行搜索，获取搜索结果
        $json = $search_obj->search();
        // 将json类型字符串解码
        $result_data = json_decode($json, true);
        $result = [];
        if (strtolower($result_data['status']) === 'ok') {
            $result = $result_data['result'];
            foreach ($result['items'] as $k => $v) {
                $result['items'][$k]['title'] = $this->set_key($v['title']);
                $result['items'][$k]['content'] = $this->set_key($v['content']);
                $result['items'][$k]['cover'] = Thumb::getThumb($v['cover'], '120x120.jpg');
            }
        }
        if (!empty($key) && $page == 1) {
            $data['doc'] = Doc::query()->where("state", 1)->where("title", "like", "%{$key}%")->orderBy("order", "desc")->get(['id', 'title', 'cover']);

            DocSearch::query()->create([
                'name'=>$key,
                'ip'=>$request->ip()
            ]);

        } else {
            $data['doc'] = [];
        }
        $data['rows'] = $rows;

        $data['result'] = $result;

        return $data;
    }

    public function title_tip(Request $request){

        $key = $request->input("key");

        $access_key = "GOkscSXVTLkhIenG";
        $secret = "OnumvS4eeijYaMlEZLok48ISMvStc9";
        $host = "http://opensearch-cn-hangzhou.aliyuncs.com";//根据自己的应用区域选择API
        $key_type = "aliyun";  //固定值，不必修改
        $opts = array('host' => $host);
        $app_name = "cloud_doc";
        $client = new CloudsearchClient($access_key, $secret, $opts, $key_type);

        $suggest = new CloudsearchSuggest($client);

        $suggest->setIndexName($app_name);
        $suggest->setSuggestName("name");
        $suggest->setHits(8);
        $suggest->setQuery($key);

        $result = json_decode($suggest->search(), true);

        return $result;
    }

    protected function set_key($str)
    {
        $str = str_replace("<span>", "<span style='color: #ff0000'>", $str);

        return $str;
    }
}
