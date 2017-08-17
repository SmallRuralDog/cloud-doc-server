<?php

namespace App\Models;

use App\Extend\Thumb;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $table = 'question';

    protected $guarded = [];

    const list_filed = ['id', 'user_id', 'title', 'pics', 'created_at', 'view_count','source','source_id'];
    //protected $appends = ['source_info'];


    public function getPicsArrAttribute()
    {
        $pics = json_decode($this->pics);
        $arr = [];
        foreach ($pics as $k => $path) {
            $arr[$k]['thumb'] = Thumb::getThumb($path, "200x200");
            $arr[$k]['path'] = Thumb::getThumb($path);
        }
        return $arr;
    }

    public function getSourceInfoAttribute()
    {
        $source = $this->source;
        $doc = [];
        switch ($source) {
            case 'doc':
                $doc = Doc::query()->find($this->source_id,['id','title','desc','cover','h_cover']);
                break;
            case 'doc-page':
                $doc_page = DocPage::query()->find($this->source_id);
                $doc = $doc_page->doc()->first(['id','title','desc','cover','h_cover']);
                break;
        }


        return $doc;

    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reply()
    {
        return $this->hasMany(QuestionReply::class);
    }

    public function getDescAttribute($key)
    {
        if (empty($key)) {
            return "如题";
        } else {
            return $key;
        }
    }
}
