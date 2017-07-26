<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/26
 * Time: 11:13
 */

namespace App\Admin\Controllers;


use App\Http\Controllers\Controller;
use QL\QueryList;

class CollectController extends Controller
{

    public function jk()
    {
        error_reporting(0);
        $url = "http://wiki.jikexueyuan.com/project/html5/";

        $client = new \GuzzleHttp\Client();

        $html = $client->get($url)->getBody();

        $rules = array(
            'title' => array('>.detail-navlist-title>a', 'text'),
            'href' => array('>.detail-navlist-title>a', 'href'),
            'list' => array('>.navul-one', 'html')
        );

        $data = QueryList::Query($html, $rules, ".detail-nav>.navlist-one")->getData(function ($item) {
            $item['list'] = QueryList::Query($item['list'], array(
                'title' => array('>.detail-navlist-title>a', 'text'),
                'href' => array('>.detail-navlist-title>a', 'href'),
                'list' => array('>.navul-two', 'html')
            ), '.detail-navlist')->getData(function ($item2) {
                $item2['list'] = QueryList::Query($item2['list'], array(
                    'title' => array('.detail-navlist-title>a', 'text'),
                    'href' => array('.detail-navlist-title>a', 'href'),
                ))->data;
                return $item2;
            });
            return $item;
        });

        return $data;
    }
}