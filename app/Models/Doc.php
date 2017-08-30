<?php

namespace App\Models;

use App\Extend\Thumb;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelFollow\Traits\CanBeLiked;

class Doc extends Model
{
    use SoftDeletes,CanBeLiked;

    protected $table = 'doc';

    protected $appends = ['cover_url','h_cover_url'];

    protected $guarded = [];

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
    public function getHCoverUrlAttribute()
    {
        return Thumb::getThumb($this->h_cover, '540x300.jpg');
    }
}
