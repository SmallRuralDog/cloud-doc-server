<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocPage extends Model
{
    use SoftDeletes;

    protected $table = 'doc_page';
    protected $guarded = [];

    public function doc()
    {
        return $this->belongsTo(Doc::class);
    }

    public function doc_menu()
    {
        return $this->belongsTo(DocMenu::class,"menu_id","id");
    }
}
