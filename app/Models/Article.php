<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'article';

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
