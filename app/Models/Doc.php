<?php

namespace App\Models;

use App\Extend\Thumb;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Doc extends Model
{
    use SoftDeletes;

    protected $table = 'doc';

    protected $appends = ['cover_url'];

    public function doc_class()
    {
        return $this->belongsTo(DocClass::class);
    }

    public function doc_menu()
    {
        return $this->hasMany(DocMenu::class);
    }

    public function doc_page()
    {
        return $this->hasMany(DocPage::class);
    }

    public function getCoverUrlAttribute()
    {
        return Thumb::getThumb($this->cover, '225x300');
    }
}
