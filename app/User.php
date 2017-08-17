<?php

namespace App;

use App\Models\WxUser;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Overtrue\LaravelFollow\Traits\CanBeFollowed;
use Overtrue\LaravelFollow\Traits\CanFollow;
use Overtrue\LaravelFollow\Traits\CanLike;

class User extends Authenticatable
{
    use Notifiable,CanFollow,CanBeFollowed,CanLike;

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
            return "云档小白";
        }else{
            return $key;
        }
    }

}
