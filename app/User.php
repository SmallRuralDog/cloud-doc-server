<?php

namespace App;

use App\Models\WxUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


    public function wx_user(){
        return $this->hasOne(WxUser::class,'user_id');
    }

    public function getTitleAttribute($key)
    {
        if(empty($key)){
            return "知识达人";
        }else{
            return $key;
        }
    }

}
