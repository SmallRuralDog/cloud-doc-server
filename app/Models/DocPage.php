<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocPage extends Model
{
    use SoftDeletes;

    protected $table = 'doc_page';
    protected $guarded = [];

    protected $appends = ['children'];

    public function doc()
    {
        return $this->belongsTo(Doc::class);
    }

    public function doc_menu()
    {
        return $this->belongsTo(DocMenu::class,"menu_id","id");
    }

    public function son(){
        return $this->hasMany(DocPage::class,'parent_id');
    }

    public function getChildrenAttribute()
    {
        return $this->son()->orderBy("order","desc")->get(['id','title','parent_id','order']);
    }
}
