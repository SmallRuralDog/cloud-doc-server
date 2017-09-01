<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/31
 * Time: 17:31
 */

namespace App\Http\Controllers\Api\Web;


use App\Http\Controllers\Api\BaseController;
use App\Models\Article;
use App\Models\ArticleTag;
use Illuminate\Http\Request;

class ArticleController extends BaseController
{
    public function index(Request $request)
    {
        $user = $this->get_user();
        return $user;
        $page = $request->input("page", 1);
        $tag_id = $request->input("tag_id", 0);
        $article = Article::query();
        $article->where('user_id', $user->id);
        $article->orderBy('created_at', 'desc');
        $list = $article->paginate(20, ['id', 'title', 'desc', 'cover', 'view_count', 'created_at', 'check_state', 'check_info', 'state']);
        foreach ($list as $v) {
            $v->tags = $v->tags()->get(['name']);
        }
        return $this->api_return(200, '', $list);
    }

    public function article_edit(Request $request)
    {
        $user = $this->get_user();
        $id = $request->input("id");

        $article = Article::query()->where("user_id", $user->id)->findOrFail($id);

        $article->tags;

        return $this->api_return(200, '', $article);
    }


    public function article_post(Request $request)
    {

        $user = $this->get_user();
        $this->validate($request, [
            'data.title' => 'required',
            'data.content' => 'required'
        ]);
        $id = $request->input("data.id", 0);
        $title = $request->input("data.title");
        $desc = $request->input("data.desc");
        $content = $request->input("data.content");
        $cover = $request->input("cover");
        $source = $request->input("source");
        $source_url = $request->input("source_url");
        $source_hash = $request->input("source_hash");


        $tags = $request->input("data.tags");
        if ($id > 0) {
            $article = Article::query()->where("user_id", $user->id)->findOrFail($id);
            $article->title = $title;
            $article->desc = $desc;
            $article->content = $content;
            if ($article->check_state == 1) {
                $article->check_state = 0;
                $article->state = 0;
            }
            $article->save();
        } else {
            $article = Article::query()->create([
                'user_id' => $user->id,
                'check_state' => 0,
                'state' => 0,
                'title' => $title,
                'desc' => $desc,
                'content' => $content,
                'source_time' => date("Y-m-d H:i:s", time())
            ]);
        }
        if ($article->id > 0) {
            if (is_array($tags)) {
                ArticleTag::query()->where('article_id', $article->id)->delete();
                foreach ($tags as $v) {
                    $ck = ArticleTag::query()->where('article_id', $article->id)->where('tag_id', $v)->first();
                    if (empty($ck)) {
                        ArticleTag::query()->insert([
                            'article_id' => $article->id,
                            'tag_id' => $v
                        ]);
                    }
                }
            }
        }
        return $this->api_return(200, '保存成功',$article->id);
    }

}