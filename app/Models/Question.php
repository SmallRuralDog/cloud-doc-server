<?php

namespace App\Models;

use App\Extend\Thumb;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';

    protected $guarded = [];

    protected $appends = ['pics_arr'];


    public function getPicsArrAttribute()
    {
         $pics = json_decode($this->pics);
         $arr = [];
         foreach ($pics as $path){
             $arr[] = Thumb::getThumb($path,"200x200");
         }
         return $arr;
    }


    public function user(){
        return $this->belongsTo(User::class);
    }
}
