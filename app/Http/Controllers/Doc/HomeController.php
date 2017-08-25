<?php
/**
 * Created by PhpStorm.
 * User: ZhangWei
 * Date: 2017/8/24
 * Time: 17:46
 */

namespace App\Http\Controllers\Doc;


use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('Doc.index');
    }


    public function home(){
        return view('Doc.home');
    }
}