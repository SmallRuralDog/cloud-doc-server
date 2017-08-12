<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    protected $table = 'user_follow';

    protected $guarded = [];


    public function doc(){
        return $this->hasOne(Doc::class,'data_id');
    }
}
