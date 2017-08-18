<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuestionReply;
use App\Models\QuestionTag;
use App\Models\Tag;
use App\User;
use Illuminate\Http\Request;

class WuKongController extends Controller
{

    public function collect(Request $request)
    {
        error_reporting(0);
        $data = $request->input('data');
        $source = $request->input('source');
        $source_id = $request->input('source_id');
        $data = json_decode($data, true);
        $question = $data['question'];

        $ans_list = $data['ans_list'];

        $user_data = $question['user'];

        $user = $this->add_user($user_data);

        if ($user->id > 0) {
            $pics = [];
            if (count($question['content']['pic_uri_list']) > 0) {
                foreach ($question['content']['pic_uri_list'] as $item) {
                    $pics[] = "https://p3.pstatp.com/list/640x360/" . $item['web_uri'];
                }
            }
            $wenda = Question::query()->updateOrCreate(['collect_id' => $question['qid']], [
                'user_id' => $user->id,
                'parent_id' => 0,
                'res_id' => 0,
                'title' => $question['title'],
                'desc' => $question['content']['text'],
                'source' => $source,
                'source_id' => $source_id,
                'pics' => json_encode($pics),
                'state' => 0,
                'is_collect' => 1,
                'collect_name' => 'wokong',
                'collect_id' => $question['qid'],
                'created_at' => date("Y-m-d H:i:s", $question['create_time'])
            ]);
            if ($wenda->id > 0) {
                if (is_array($question['concern_tags'])) {
                    foreach ($question['concern_tags'] as $tag) {
                        $v = $tag['name'];
                        $tag = Tag::query()->firstOrCreate(['name' => $v], ['name' => $v]);
                        $ck = QuestionTag::query()->where('question_id', $wenda->id)->where('tag_id', $tag->id)->first();
                        if (empty($ck)) {
                            QuestionTag::query()->insert([
                                'question_id' => $wenda->id,
                                'tag_id' => $tag->id
                            ]);
                        }
                    }
                }

                foreach ($ans_list as $item) {
                    $i_user_data = $item['user'];
                    $i_user = $this->add_user($i_user_data);
                    if ($i_user->id > 0) {
                        QuestionReply::query()->updateOrCreate(['collect_id' => $item['ansid']], [
                            'user_id' => $i_user->id,
                            'question_id' => $wenda->id,
                            'content' => $item['content'],
                            'state' => 1,
                            'is_accept' => 0,
                            'is_collect' => 1,
                            'collect_name' => 'wokong',
                            'collect_id' => $item['ansid'],
                            'created_at' => date("Y-m-d H:i:s", $item['create_time'])
                        ]);
                    }
                }
            }
            return ['id' => $wenda->id, 'user_id' => $user->id];
        }
    }

    private function add_user($user_data)
    {
        $user = User::query()->updateOrCreate(['robot_id' => $user_data['user_id']], [
            'is_robot' => 1,
            'robot_id' => $user_data['user_id'],
            'name' => $user_data['uname'],
            'email' => $user_data['user_id'] . '@leyix.com',
            'password' => bcrypt($user_data['user_id']),
            'is_auth' => 0,
            'wx_user_id' => 0,
            'avatar' => $user_data['avatar_url'],
            'title' => $user_data['description'],
        ]);

        return $user;
    }

}