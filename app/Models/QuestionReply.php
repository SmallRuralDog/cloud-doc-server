<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelFollow\Traits\CanBeLiked;

class QuestionReply extends Model
{
    use CanBeLiked;
    protected $table = 'question_reply';
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
