<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/7/26
 * Time: 11:13
 */

namespace App\Admin\Controllers;


use App\Extend\AliMNS;
use App\Http\Controllers\Controller;
use App\Models\DocPage;
use Illuminate\Http\Request;
use QL\QueryList;

class CollectController extends Controller
{

    public function w3c(Request $request)
    {
        $url = $request->input("url");
        $doc_id = $request->input("doc_id", 0);
        if ($doc_id <= 0) {
            return response()->json(['status_code' => 0, 'message' => 'error']);
        }
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
                ), '>.dd-item')->getData(function ($item3) {
                    $item3['list'] = QueryList::Query($item3['list'], array(
                        'title' => array('>.dd-content>a', 'text'),
                        'href' => array('>.dd-content>a', 'href'),
                        'list' => array('>.dd-list', 'html')
                    ), '>.dd-item')->data;
                    return $item3;
                });
                return $item2;
            });
            return $item;
        });

        $host = "https://www.w3cschool.cn";
        foreach ($data as $k => $v) {
            if ($v['href'] == "#" || empty($v['href'])) {
                $collect_id = md5($doc_id . $v['title'] . "#");
                $collect_state = 1;
            } else {
                $v['href'] = $host . $v['href'];
                $collect_id = md5($v['href']);
                $collect_state = 0;
            }
            $page = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => 0,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['href'],
                'collect_state' => $collect_state
            ]);
            if (!empty($v['list']) && $page->id > 0) {
                $this->w3c_children($v['list'], $page->id, $doc_id, $host);
            }
        }
    }

    private function w3c_children($data, $parent_id, $doc_id, $host)
    {
        foreach ($data as $k => $v) {
            if ($v['href'] == "#" || empty($v['href'])) {
                $collect_id = md5($doc_id . $v['title'] . "#");
                $collect_state = 1;
            } else {
                $v['href'] = $host . $v['href'];
                $collect_id = md5($v['href']);
                $collect_state = 0;
            }
            $page = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => $parent_id,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['href'],
                'collect_state' => $collect_state
            ]);
            if (!empty($v['list']) && $page->id > 0) {
                $this->w3c_children($v['list'], $page->id, $doc_id, $host);
            }
        }
    }


    public function ky(Request $request)
    {
        error_reporting(0);
        $url = $request->input("url");
        $doc_id = $request->input("doc_id", 0);
        if ($doc_id <= 0) {
            return response()->json(['status_code' => 0, 'message' => 'error']);
        }
        $client = new \GuzzleHttp\Client();
        $html = $client->get($url)->getBody();
        preg_match_all("/<script type=\"application\/payload\+json\">(.*?)<\/script>/is", $html, $json);

        $data = $json[1][0];

        $data = json_decode($data, true);

        $data = $data['catalog'];

        $info = pathinfo($url);

        $host = $info['dirname'];
        $msn = new AliMNS();
        $msn->create("cloud-doc-collect-ky", "cloud-doc-collect-ky", "https://cloud-doc.leyix.com/collect/ky");
        foreach ($data as $k => $v) {
            if ($v['id'] == "#" || empty($v['id'])) {
                $collect_id = md5($doc_id . $v['title'] . $v['id']);
                $collect_state = 1;
            } else {
                $v['id'] = $host . '/' . $v['id'];
                $collect_id = md5($v['id']);
                $collect_state = 0;
            }
            $page = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => 0,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['id'],
                'collect_state' => $collect_state
            ]);
            $msn->send_message("cloud-doc-collect-ky", $page->id);
            if (!empty($v['children']) && $page->id > 0) {
                $this->ky_children($v['children'], $page->id, $msn, $doc_id, $host);
            }
        }
    }

    private function ky_children($data, $parent_id, $msn, $doc_id, $host)
    {
        foreach ($data as $k => $v) {
            if ($v['id'] == "#" || empty($v['id'])) {
                $collect_id = md5($doc_id . $v['title'] . $v['id']);
                $collect_state = 1;
            } else {
                $v['id'] = $host . '/' . $v['id'];
                $collect_id = md5($v['id']);
                $collect_state = 0;
            }
            $page = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => $parent_id,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['id'],
                'collect_state' => $collect_state
            ]);
            $msn->send_message("cloud-doc-collect-ky", $page->id);
            if (!empty($v['children']) && $page->id > 0) {
                $this->ky_children($v['children'], $page->id, $msn, $doc_id, $host);
            }
        }
    }

    public function sc(Request $request)
    {
        error_reporting(0);
        $url = $request->input("url");
        $doc_id = $request->input("doc_id", 0);
        if ($doc_id <= 0) {
            return response()->json(['status_code' => 0, 'message' => 'error']);
        }

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
        $host = "http://www.shouce.ren";
        $msn = new AliMNS();
        $msn->create("cloud-doc-collect-sc", "cloud-doc-collect-sc", "https://cloud-doc.leyix.com/collect/sc");
        foreach ($data as $k => $v) {
            if ($v['href'] == "#" || empty($v['href'])) {
                $collect_id = md5($doc_id . $v['title'] . $v['href']);
                $collect_state = 1;
            } else {
                $v['href'] = $host . $v['href'];
                $collect_id = md5($v['href']);
                $collect_state = 0;
            }
            $page_1 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => 0,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['href'],
                'collect_state' => $collect_state
            ]);

            $msn->send_message("cloud-doc-collect-sc", $page_1->id);
            if (!empty($v['list']) && $page_1->id > 0) {
                foreach ($v['list'] as $kk => $vv) {
                    if ($vv['href'] == "#" || empty($vv['href'])) {
                        $collect_id = md5($doc_id . $vv['title'] . $vv['href']);
                        $collect_state = 1;
                    } else {
                        $vv['href'] = $host . $vv['href'];
                        $collect_id = md5($vv['href']);
                        $collect_state = 0;
                    }
                    $page_2 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                        'title' => $vv['title'],
                        'parent_id' => $page_1->id,
                        'menu_title' => $vv['title'],
                        'content' => "#" . $vv['title'],
                        'order' => 99999 - $kk,
                        'state' => 0,
                        'doc_id' => $doc_id,
                        'menu_id' => 0,
                        'collect_url' => $vv['href'],
                        'collect_state' => $collect_state
                    ]);
                    $msn->send_message("cloud-doc-collect-sc", $page_2->id);
                    if (!empty($vv['list']) && $page_2->id > 0) {
                        foreach ($v['list'] as $kkk => $vvv) {
                            if ($vvv['href'] == "#" || empty($vv['href'])) {
                                $collect_id = md5($doc_id . $vvv['title'] . $vvv['href']);
                                $collect_state = 1;
                            } else {
                                $vvv['href'] = $host . $vvv['href'];
                                $collect_id = md5($vvv['href']);
                                $collect_state = 0;
                            }
                            $page_3 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                                'title' => $vvv['title'],
                                'parent_id' => $page_2->id,
                                'menu_title' => $vvv['title'],
                                'content' => "#" . $vvv['title'],
                                'order' => 99999 - $kkk,
                                'state' => 0,
                                'doc_id' => $doc_id,
                                'menu_id' => 0,
                                'collect_url' => $vvv['href'],
                                'collect_state' => $collect_state
                            ]);
                            $msn->send_message("cloud-doc-collect-sc", $page_3->id);
                        }
                    }
                }
            }
        }

        return ['state' => true];
    }

    public function jk(Request $request)
    {
        error_reporting(0);
        $url = $request->input("url");
        $doc_id = $request->input("doc_id", 0);
        if ($doc_id <= 0) {
            return response()->json(['status_code' => 0, 'message' => 'error']);
        }

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
        $msn = new AliMNS();
        $msn->create("cloud-doc-collect", "cloud-doc-collect", "https://cloud-doc.leyix.com/collect/jike");
        foreach ($data as $k => $v) {
            if ($v['href'] == "#") {
                $collect_id = md5($doc_id . $v['title'] . $v['href']);
                $collect_state = 1;
            } else {
                $collect_id = md5($v['href']);
                $collect_state = 0;
            }
            $page_1 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                'title' => $v['title'],
                'parent_id' => 0,
                'menu_title' => $v['title'],
                'content' => "#" . $v['title'],
                'order' => 99999 - $k,
                'state' => 0,
                'doc_id' => $doc_id,
                'menu_id' => 0,
                'collect_url' => $v['href'],
                'collect_state' => $collect_state
            ]);
            $msn->send_message("cloud-doc-collect", $page_1->id);
            if (!empty($v['list']) && $page_1->id > 0) {
                foreach ($v['list'] as $kk => $vv) {
                    if ($vv['href'] == "#") {
                        $collect_id = md5($doc_id . $vv['title'] . $vv['href']);
                        $collect_state = 1;
                    } else {
                        $collect_id = md5($vv['href']);
                        $collect_state = 0;
                    }
                    $page_2 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                        'title' => $vv['title'],
                        'parent_id' => $page_1->id,
                        'menu_title' => $vv['title'],
                        'content' => "#" . $vv['title'],
                        'order' => 99999 - $kk,
                        'state' => 0,
                        'doc_id' => $doc_id,
                        'menu_id' => 0,
                        'collect_url' => $vv['href'],
                        'collect_state' => $collect_state
                    ]);
                    $msn->send_message("cloud-doc-collect", $page_2->id);
                    if (!empty($vv['list']) && $page_2->id > 0) {
                        foreach ($v['list'] as $kkk => $vvv) {
                            if ($vvv['href'] == "#") {
                                $collect_id = md5($doc_id . $vvv['title'] . $vvv['href']);
                                $collect_state = 1;
                            } else {
                                $collect_id = md5($vvv['href']);
                                $collect_state = 0;
                            }
                            $page_3 = DocPage::query()->firstOrCreate(['collect_id' => $collect_id], [
                                'title' => $vvv['title'],
                                'parent_id' => $page_2->id,
                                'menu_title' => $vvv['title'],
                                'content' => "#" . $vvv['title'],
                                'order' => 99999 - $kkk,
                                'state' => 0,
                                'doc_id' => $doc_id,
                                'menu_id' => 0,
                                'collect_url' => $vvv['href'],
                                'collect_state' => $collect_state
                            ]);
                            $msn->send_message("cloud-doc-collect", $page_3->id);
                        }
                    }
                }
            }
        }

        return ['state' => true];
    }
}