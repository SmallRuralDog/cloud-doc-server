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
use App\Models\DocClass;

class DocController extends Controller
{

    public function index()
    {
        $swiper = Ad::query()->where('loca_id',1)
            ->where('state',1)->orderBy('order','desc')->limit(5)->get(['id','title','page','cover','loca_id']);

        $res['swiper'] = $swiper;


        $grid = Ad::query()->where('loca_id',2)
            ->where('state',1)->orderBy('order','desc')->limit(5)->get(['id','title','page','cover','loca_id']);

        $res['grid'] = $grid;



        $doc = DocClass::query()->where('parent_id',1)->where('state',1)->get(['id','title','desc']);
        foreach ($doc as $v){
            $v->doc = $v->doc()->orderBy("doc.order", "desc")->orderBy("doc.id")->where("doc.state", "=", 1)->where("doc.is_hot", 1)->limit(4)->get( ['doc.id', 'doc.title', 'doc.desc', 'doc.cover', 'doc.is_end', 'doc.is_hot', 'doc.doc_class_id']);
            foreach ($v->doc as $vv){
                $vv->view_count = $vv->doc_page()->sum("view_count");
            }
        }

        $res['doc'] = $doc;
        return response()->json($res);
    }
}