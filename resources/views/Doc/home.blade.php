@extends('layouts.doc-base')
@section('title', '我的文档')
@section('content')
    <div id="AuthorProfile">
        <div class="pagehead">
            <div class="container">
                <div class="overview">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-2">
                                <figure class="avatar">
                                    <img src="{{Auth::user()->avatar}}">
                                </figure>
                            </div>
                            <div class="col-md-7">
                                <h1 class="overview-title">
                                    <span>{{Auth::user()->name}}</span>
                                </h1>
                                <p class="overview-description">
                                    <a href="#"><em class="text-muted">{{Auth::user()->title}}</em></a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-9">
                            <ul class="menu pull-left">
                                <li class="active">
                                    <a><i class="Icon octicon octicon-book"></i>我的文档</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-3">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="gb-page-inner">
            <div>
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="Books">
                                <div class="Books">
                                    <div class="Book">
                                        <div class="book-template">
                                            <i class="iconfont icon-wendang"></i>
                                        </div>
                                        <div class="book-infos">
                                            <h2 class="title"><a href="welcome.html">818</a></h2>
                                            <p class="description">45451515</p>
                                            <p class="updated">Created<span>3 days ago</span>
                                            </p>
                                        </div>
                                        <div class="book-actions">
                                            <div class="btn-toolbar ">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default btn-md" >编辑</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection