<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelFollow\Traits\CanBeLiked;

class DocPage extends Model
{
    use CanBeLiked;

    protected $table = 'doc_page';
    protected $guarded = [];

    protected $appends = ['children'];

    public function doc()
    {
        return $this->belongsTo(Doc::class, "doc_id", "id");
    }

    public function doc_menu()
    {
        return $this->belongsTo(DocMenu::class, "menu_id", "id");
    }

    public function son()
    {
        return $this->hasMany(DocPage::class, 'parent_id');
    }

    public function getChildrenAttribute()
    {
        return $this->son()->orderBy("order", "desc")->get(['id', 'title', 'menu_title', 'parent_id', 'order']);
    }


}
