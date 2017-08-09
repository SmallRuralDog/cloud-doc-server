<?php

namespace App\Models;

use App\Extend\Thumb;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'article';

    protected $appends = ['cover_url'];

    protected $guarded = [];

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getCoverUrlAttribute()
    {
        return Thumb::getThumb($this->cover);
    }
}
