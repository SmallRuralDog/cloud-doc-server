<?php

namespace App\Models;

use App\Extend\Thumb;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';

    protected $guarded = [];

    //protected $appends = ['pics_arr'];


    public function getPicsArrAttribute()
    {
         $pics = json_decode($this->pics);
         $arr = [];
         foreach ($pics as $k=>$path){
             $arr[$k]['thumb'] = Thumb::getThumb($path,"200x200");
             $arr[$k]['path'] = Thumb::getThumb($path);
         }
         return $arr;
    }


    public function user(){
        return $this->belongsTo(User::class);
    }

    public function reply(){
        return $this->hasMany(QuestionReply::class);
    }
    public function getDescAttribute($key)
    {
        if(empty($key)){
            return "如题";
        }else{
            return $key;
        }
    }
}
