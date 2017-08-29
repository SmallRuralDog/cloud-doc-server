<?php

namespace App\Models;

use App\Extend\Thumb;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class DocClass extends Model
{
    use ModelTree;
    protected $table = 'doc_class';
    protected $appends = ['icon_url'];

    public function son()
    {
        return $this->hasMany(DocClass::class, "parent_id");
    }

    public function getIconUrlAttribute()
    {
        return Thumb::getThumb($this->icon, "120x120.jpg");
    }


    public function doc()
    {
        return $this->hasManyThrough(Doc::class, DocClass::class, 'parent_id', 'doc_class_id', 'id');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    //获取某个分类的所有子分类
    public static function getSubs($categorys,$catId=0,$level=1){
        $subs=array();
        foreach($categorys as $item){
            if($item['parent_id']==$catId){
                $item['level']=$level;
                $subs[]=$item;
                $subs=array_merge($subs,self::getSubs($categorys,$item['id'],$level+1));

            }
        }
        return $subs;
    }
}
