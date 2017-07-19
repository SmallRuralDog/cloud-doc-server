<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocMenu extends Model
{
    protected $table = 'doc_menu';

    public function doc()
    {
        return $this->belongsTo(Doc::class);
    }

    public function doc_page()
    {
        return $this->hasMany(DocPage::class,"menu_id","id");
    }
}
