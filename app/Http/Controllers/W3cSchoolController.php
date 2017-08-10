<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/10
 * Time: 15:17
 */

namespace App\Http\Controllers;


use QL\QueryList;

class W3cSchoolController extends Controller
{




    public function index()
    {
        error_reporting(0);
        $url = "https://www.w3cschool.cn/weixinapp/";

        $client = new \GuzzleHttp\Client();

        $html = $client->get($url)->getBody();

        $rules = array(
            'title' => array('>.dd-content>a', 'text'),
            'href' => array('>.dd-content>a', 'href'),
            'list' => array('>.dd-list', 'html')
        );

        $data = QueryList::Query($html, $rules, ".dd>.dd-list>.dd-item")->getData(function ($item) {
            $item['list'] = QueryList::Query($item['list'], array(
                'title' => array('>.dd-content>a', 'text'),
                'href' => array('>.dd-content>a', 'href'),
                'list' => array('>.dd-list', 'html')
            ), '>.dd-item')->getData(function ($item2) {
                $item2['list'] = QueryList::Query($item2['list'], array(
                    'title' => array('>.dd-content>a', 'text'),
                    'href' => array('>.dd-content>a', 'href'),
                    'list' => array('>.dd-list', 'html')
                ),'>.dd-item')->getData(function ($item3){
                    $item3['list'] = QueryList::Query($item3['list'], array(
                        'title' => array('>.dd-content>a', 'text'),
                        'href' => array('>.dd-content>a', 'href'),
                        'list' => array('>.dd-list', 'html')
                    ),'>.dd-item')->data;
                    return $item3;
                });
                return $item2;
            });
            return $item;
        });

        return $data;
    }
}