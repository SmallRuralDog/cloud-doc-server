<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/8
 * Time: 16:43
 */

namespace App\Http\Controllers\Api\V3;


use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $article = Article::query();

        $list = $article->paginate(20, ['id', 'title', 'desc', 'cover', 'view_count']);

        foreach ($list as $v) {
            $v->tags = $v->tags()->get(['tag.id', 'tag.name']);
        }
        $swiper = Ad::query()->where('loca_id', 1)
            ->where('state', 1)->orderBy('order', 'desc')->limit(5)->get(['id', 'title', 'page', 'cover', 'loca_id', 'open_type']);

        return response()->json($list);
    }

}