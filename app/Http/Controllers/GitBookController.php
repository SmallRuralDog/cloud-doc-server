<?php

namespace App\Http\Controllers;

use QL\QueryList;

/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/22
 * Time: 9:26
 */
class GitBookController extends Controller
{
    public function index()
    {
        error_reporting(0);
        $url = "https://sfantasy.gitbooks.io/node-in-action/content/zh/";



        $client = new \GuzzleHttp\Client();

        $html = $client->get($url)->getBody();

        $rules = array(
            'title' => array('>a', 'text'),
            'href' => array('', 'data-path'),
            'list' => array('>.articles', 'html')
        );


        $data = QueryList::Query($html, $rules, ".summary>.chapter")->getData(function ($item) use ($url) {
            $item['title'] = $this->set_title($item['title']);
            $item['href'] = $this->set_href($url, $item['href']);
            $item['list'] = QueryList::Query($item['list'], array(
                'title' => array('>a', 'text'),
                'href' => array('', 'data-path'),
                'list' => array('>.articles', 'html')
            ), '>.chapter ')->getData(function ($item2) use ($url) {
                $item2['title'] = $this->set_title($item2['title']);
                $item2['href'] = $this->set_href($url, $item2['href']);
                $item2['list'] = QueryList::Query($item2['list'], array(
                    'title' => array('>a', 'text'),
                    'href' => array('', 'data-path'),
                    'list' => array('>.articles', 'html')
                ), '>.chapter')->getData(function ($item3) use ($url) {
                    $item3['title'] = $this->set_title($item3['title']);
                    $item3['href'] = $this->set_href($url, $item3['href']);
                    $item3['list'] = QueryList::Query($item3['list'], array(
                        'title' => array('>a', 'text'),
                        'href' => array('', 'data-path'),
                        'list' => array('>.articles', 'html')
                    ), '>.chapter')->data;
                    return $item3;
                });
                return $item2;
            });

            return $item;
        });
        return $data;
    }


    private function set_title($title)
    {
        return str_replace(["\n", " "], "", trim($title));
    }

    private function set_href($url, $href)
    {

        return $url  . $href;
    }
}