<?php


namespace App\Http\Controllers\Api\V2;


use App\Http\Controllers\Controller;
use App\Models\DocClass;

class DocController extends Controller
{

    public function class_list()
    {
        $doc_class_list = DocClass::query()->where("parent_id", 1)->get(['id','title']);

        foreach ($doc_class_list as $v){
            $v->son = $v->son()->get(['id','title','icon']);
        }

        return $doc_class_list;
    }
}