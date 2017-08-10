<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class WxUser
 * @package App\Models
 * @mixin \Eloquent
 */
class WxUser extends Model
{
    protected $table = 'wx_user';
    protected $guarded = [];
}
