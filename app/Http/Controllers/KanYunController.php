<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/31
 * Time: 10:00
 */

namespace App\Http\Controllers;


use App\Extend\AliMNS;
use App\Models\DocPage;

class KanYunController extends Controller
{

    public function collect()
    {
        error_reporting(0);
        $mns = new AliMNS();
        $id = $mns->get_message()->Message;
        $page = DocPage::query()->find($id);
        if (empty($page)) {
            http_response_code(200);
            return "文章不存在";
        }
        $url = $page->collect_url;
        if ($page->collect_state != 0) {
            http_response_code(200);
            return "文章已采集";
        }

        $client = new \GuzzleHttp\Client();
        $data = $client->get($url, ['headers' => ['x-requested-with' => 'XMLHttpRequest']])->getBody();
        $data = json_decode($data);


        $page->content = $data->content;
        $page->collect_state = 1;
        $page->state = 1;

        $page->save();
        http_response_code(200);
        return "采集成功";
    }

    public function index()
    {
        $url = "https://www.kancloud.cn/manual/thinkphp5/118003";

        $client = new \GuzzleHttp\Client();

        $html = $client->get($url)->getBody();

        preg_match_all("/<script type=\"application\/payload\+json\">(.*?)<\/script>/is", $html, $json);

        $data = $json[1][0];

        $data = json_decode($data, true);

        $data = $data['catalog'];

        $info = pathinfo($url);

        $dirname = $info['dirname'];

        foreach ($data as $v) {

        }

        return $data;
    }

}