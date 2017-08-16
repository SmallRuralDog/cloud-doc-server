<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class QuestionReply extends Model
{
    protected $table = 'question_reply';
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class);
    }
}
