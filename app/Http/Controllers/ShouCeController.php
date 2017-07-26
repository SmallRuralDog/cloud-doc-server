<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/26
 * Time: 19:54
 */

namespace App\Http\Controllers;


use App\Extend\AliMNS;
use App\Models\DocPage;
use League\HTMLToMarkdown\HtmlConverter;
use QL\QueryList;

class ShouCeController extends Collect
{
    public function collect(){
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
        $html = $client->get($url)->getBody();
        $rules = array(
            'content' => array('#post-content', 'html'),
        );
        $data = QueryList::Query($html, $rules)->data;

        $content = $data[0]['content'];
        $content = $this->formaturl($content, $url);

        $content = preg_replace("/<ul class=\"pager\">(.*?)<\/ul>/is", "", $content);

        $content = preg_replace("/<\/pre>/is", "</pre>\n\n", $content);
        $content = preg_replace("/<\/code>/is", "</code>\n\n", $content);
        $content = preg_replace("/&lt;(.*?)&gt;/is", "<c-code>&lt;$1&gt;</c-code>", $content);
        preg_match_all("/<code.*?>(.*?)<\/code>/is", $content, $h_arg);
        preg_match_all("/<table.*?>(.*?)<\/table>/is", $content, $t_arg);

        foreach ($h_arg[0] as $v) {
            if ($v != null) {
                $content = str_replace($v, $this->_setCode($v), $content);
            }
        }
        $content = preg_replace("/<c-code>/is", "<code>", $content);
        $content = preg_replace("/<\/c-code>/is", "</code>", $content);

        //dd($content);

        $converter = new HtmlConverter();


        $markdown = $converter->convert($content);

        $markdown = str_replace("</body></html>","",$markdown);
        preg_match_all("/<table.*?>(.*?)<\/table>/is", $markdown, $arg);
        foreach ($arg as $v) {
            if ($v[0] != null) {
                $markdown = preg_replace("/<table.*?>(.*?)<\/table>/is", $this->_setTable($v[0]), $markdown);
            }
        }
        $page->content = $markdown;
        $page->collect_state = 1;
        $page->state = 1;

        $page->save();
        http_response_code(200);
        return "采集成功";
    }

    private function _setCode($code)
    {

        $code = preg_replace("/<c-code>/is", "", $code);
        $code = preg_replace("/<\/c-code>/is", "", $code);
        return $code;
    }

















    public function index()
    {
        error_reporting(0);
        $url = "http://www.shouce.ren/api/view/a/6431";

        $client = new \GuzzleHttp\Client();

        $html = $client->get($url)->getBody();

        $rules = array(
            'title' => array('>div', 'text'),
            'href' => array('>div>a', 'href'),
            'list' => array('>ul', 'html')
        );

        $data = QueryList::Query($html, $rules, "#nav-sctree>.list-group>li")->getData(function ($item) {
            $item['list'] = QueryList::Query($item['list'], array(
                'title' => array('>div', 'text'),
                'href' => array('>div>a', 'href'),
                'list' => array('>ul', 'html')
            ), '>li')->getData(function ($item_1) {
                $item_1['list'] = QueryList::Query($item_1['list'], array(
                    'title' => array('>div', 'text'),
                    'href' => array('>div>a', 'href'),
                    'list' => array('>ul', 'html')
                ), '>li')->data;
                return $item_1;
            });
            return $item;
        });

        return $data;
    }

}