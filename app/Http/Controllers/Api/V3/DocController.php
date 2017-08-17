<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/8
 * Time: 11:16
 */

namespace App\Http\Controllers\Api\V3;

use App\Http\Controllers\Api\BaseController;
use App\Models\Ad;
use App\Models\Article;
use App\Models\ArticleTag;
use App\Models\Doc;
use App\Models\DocClass;
use App\Models\DocClassTag;
use App\Models\DocPage;
use App\Models\Question;
use App\Models\UserFollow;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DocController extends BaseController
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
        $doc->orderBy("order", "desc")->orderBy("id", "desc");
        $doc->where("doc_class_id", $class_id);
        $doc_list = $doc->get(['id', 'title', 'desc', 'cover', 'is_end', 'is_hot', 'doc_class_id']);

        foreach ($doc_list as $k => $v) {
            $doc_list[$k]->view_count = $v->doc_page()->sum("view_count");
        }
        $tag_ids = DocClassTag::query()->where('doc_class_id', $class_id)->get(['tag_id'])->pluck('tag_id')->toArray();
        $article_ids = ArticleTag::query()->whereIn('tag_id', $tag_ids)->get(['article_id'])->pluck('article_id')->toArray();
        $article = Article::query();
        if (is_array($article_ids)) {
            $article->whereIn('id', $article_ids);
        }
        $article->orderBy('source_time', 'desc');

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

    public function info(Request $request)
    {
        $doc_id = $request->input("doc_id");
        $doc = Doc::query()->find($doc_id);
        $doc->doc_class;

        $doc->user = [
            'nick_name' => '云档'
        ];
        $user = $this->get_user();
        if ($user) {
            $ck = UserFollow::query()->where([
                'user_id' => $user->id,
                'data_id' => $doc_id,
                'type' => 'doc'
            ])->first();

            $doc->is_follow = empty($ck) ? false : true;

        } else {
            $doc->is_follow = false;
        }

        return $this->api_return(200, '', $doc);
    }

    public function info_2(Request $request)
    {
        $doc_id = $request->input("doc_id");
        $page = $request->input("page", 1);
        $doc = Doc::query()->find($doc_id);
        $doc->doc_class;

        $doc->user = [
            'nick_name' => '云档'
        ];
        $user = $this->get_user();
        if ($user) {
            $ck = UserFollow::query()->where([
                'user_id' => $user->id,
                'data_id' => $doc_id,
                'type' => 'doc'
            ])->first();

            $doc->is_follow = empty($ck) ? false : true;
            $doc->is_like = $user->hasLiked($doc);

        } else {
            $doc->is_follow = false;
            $doc->is_like = false;

        }
        $doc->like_count = $doc->likers()->count();
        $doc->likes = $doc->likers()->limit(30)->get(['id', 'name', 'avatar']);

        $son_ids = DocPage::query()->where('doc_id', $doc_id)->where('state', 1)->get(['id'])->pluck('id');

        $question = Question::query()->where(function ($query) use ($doc_id) {
            $query->where('source', 'doc')->where('source_id', $doc_id)->where('state',1);
        })->orWhere(function ($query) use ($son_ids) {
            $query->where('source', 'doc-page')->whereIn('source_id', $son_ids)->where('state',1);
        });

        $question->orderBy('created_at', 'desc');

        $question_page = $question->paginate(15, Question::list_filed);

        foreach ($question_page as $v) {
            $v->user = $v->user()->first(['id', 'name', 'title', 'avatar']);

            $v->created = Carbon::parse($v->created_at)->diffForHumans();
            $v->pics_arr = $v->pics_arr;
            $v->pics_type = count($v->pics_arr) % 3 == 0 ? 3 : count($v->pics_arr) % 3;
            $v->reply_count = $v->reply()->count();
        }

        if ($page == 1) {
            $json = json_encode($question_page);
            $question_page = json_decode($json);
            $question_page->doc = $doc;
        }

        return response()->json($question_page);
    }
}