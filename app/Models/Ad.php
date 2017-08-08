<?php

namespace App\Models;

use App\Extend\Thumb;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $table = 'ad';
    protected $appends = ['cover_url'];
    protected $hidden = ['ad_loca'];

    public function getCoverUrlAttribute()
    {

        return Thumb::getThumb($this->cover, $this->ad_loca->style);
    }

    public function ad_loca(){
        return $this->belongsTo(AdLoca::class,'loca_id');
    }
}
