<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/8
 * Time: 11:16
 */

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Doc;
use App\Models\DocClass;
use App\Models\DocClassTag;
use Illuminate\Http\Request;

class DocController extends Controller
{

    public function index()
    {
        $swiper = Ad::query()->where('loca_id', 1)
            ->where('state', 1)->orderBy('order', 'desc')->limit(5)->get(['id', 'title', 'page', 'cover', 'loca_id', 'open_type']);

        $res['swiper'] = $swiper;


        $grid = Ad::query()->where('loca_id', 2)
            ->where('state', 1)->orderBy('order', 'desc')->limit(5)->get(['id', 'title', 'page', 'cover', 'loca_id', 'open_type']);

        $res['grid'] = $grid;


        $doc = DocClass::query()->where('parent_id', 1)->where('index_show', 1)->where('state', 1)->get(['id', 'title', 'desc']);
        foreach ($doc as $v) {
            $v->doc = $v->doc()->orderBy("doc.order", "desc")->orderBy("doc.id")->where("doc.state", "=", 1)->where("doc.is_hot", 1)->limit(6)->get(['doc.id', 'doc.title', 'doc.desc', 'doc.cover', 'doc.h_cover', 'doc.is_end', 'doc.is_hot', 'doc.doc_class_id']);
            foreach ($v->doc as $vv) {
                $vv->view_count = $vv->doc_page()->sum("view_count");
            }
        }

        $res['doc'] = $doc;
        return response()->json($res);
    }

    public function doc_class_list(Request $request)
    {
        $page = $request->input("page", 1);

        $class_id = $request->input("class_id");
        $doc = Doc::query();
        $doc->where("state", "=", 1);
        $doc->orderBy("order", "desc")->orderBy("id");
        $doc->where("doc_class_id", $class_id);
        $doc_list = $doc->get(['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        foreach ($doc_list as $k => $v) {
            $doc_list[$k]->view_count = $v->doc_page()->sum("view_count");
        }
        $tag_ids = DocClassTag::query()->where('doc_class_id',$class_id)->get(['tag_id'])->pluck('tag_id')->toArray();
        $article_ids = ArticleTag::query()->whereIn('tag_id', $tag_ids)->get(['article_id'])->pluck('article_id')->toArray();
        $article = Article::query();
        if (is_array($article_ids)) {
            $article->whereIn('id', $article_ids);
        }
        $article->orderBy('source_time','desc');

        $list = $article->paginate(20, ['id', 'title', 'desc', 'cover', 'view_count']);
        foreach ($list as $v) {
            $v->tags = $v->tags()->get(['tag.id', 'tag.name']);
        }

        if ($page == 1) {
            $json = json_encode($list);

            $list = json_decode($json);

            $list->doc_list = $doc_list;
        }



        return response()->json($list);
    }
}