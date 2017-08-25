<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>
        云档 · 在线文档
    </title>
    <link rel="stylesheet" href="{{asset('doc/css/style.css')}}"/>
    <link rel="stylesheet" href="{{asset('doc/css/yundang.css')}}">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <meta property="twitter:creator" content="@GitBookIO"/>
</head>
<body>
<div id="application">
    <div class="PJAXWrapper" data-reactroot="" data-reactid="1" data-react-checksum="621823924">
        <div id="Homepage" data-reactid="2">
            <div class="gb-page-wrapper" data-reactid="3">
                <div class="gb-page-header" data-reactid="4">
                    <div class="container" data-reactid="5">
                        <a href="{{route('index')}}" class="logo pull-left" data-reactid="6">
                            <img src="{{asset('doc/image/logob2.png')}}" >
                        </a>
                        <div data-reactid="9">
                            @if(Auth()->guest())
                                <a href="{{route('login')}}" class="btn btn-link btn-md pull-right hidden-xs btn-login">
                                    登录
                                </a>
                            @else
                                <a href="{{route('home')}}" class="btn btn-link btn-md pull-right hidden-xs btn-login">
                                    {{Auth::user()->name}}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="gb-page-body" data-reactid="34">
                    <div class="Intro" data-reactid="35">
                        <div class="container" data-reactid="36">
                            <div class="row" data-reactid="37">
                                <div class="col-md-12" data-reactid="38">
                                    <div class="HomeTitle" data-reactid="39">
                                        <h1 data-reactid="40">
                                            云档微信小程序
                                        </h1>
                                        <p class="lead" data-reactid="41">
                                            云档提供在线文档，在微信小程序上阅读
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="row" data-reactid="42">
                                <div class="col-md-4" data-reactid="43" style="text-align: center;">

                                    <img src="{{asset('doc/image/gh_52a837af05b4_860.jpg')}}" style="width:300px;">


                                    <p class="WithGithub" data-reactid="59">
                                        <a href="dashboard.html" data-reactid="60">
                                            微信小程序扫码登录
                                        </a>
                                    </p>
                                    <hr data-reactid="64"/>
                                    <p class="text-center" data-reactid="65">

                                    </p>
                                </div>
                                <div class="col-md-8" data-reactid="66">
                                    <img class="EditorPreview" src="{{asset('doc/image/work.png')}}" data-reactid="67"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="Features" data-reactid="68">
                        <div class="container" data-reactid="69">
                            <div class="row" data-reactid="70">
                                <div class="col-xs-12 col-sm-4" data-reactid="71">
                                    <div class="Feature" data-reactid="72">

                                        <i class="iconfont icon-tuandui"></i>
                                        <h3 data-reactid="74">
                                            Made for teams
                                        </h3>
                                        <p data-reactid="75">
                                            GitBook helps structure your workflow, securing access control and content
                                            review.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4" data-reactid="76">
                                    <div class="Feature" data-reactid="77">
                                        <i class="iconfont icon-bijibendiannaolaptop114"></i>
                                        <h3 data-reactid="79">
                                            简单而美丽
                                        </h3>
                                        <p data-reactid="80">
                                            使用我们美丽强大的网页或桌面编辑器轻松创建文档
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-4" data-reactid="81">
                                    <div class="Feature" data-reactid="82">
                                        <i class="iconfont icon-shizhong"></i>
                                        <h3 data-reactid="84">
                                            Version control
                                        </h3>
                                        <p data-reactid="85">
                                            Never lose any of your data, all changes are backed up by modern version
                                            control.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="gb-page-footer" data-reactid="164">
                    <div class="Footer" data-reactid="165">
                        <div class="container" data-reactid="166">
                            <div class="footer-logo" data-reactid="167">
                                <img src="{{asset('doc/image/logo-bottom.png')}}">

                            </div>
                            <ul class="menu" data-reactid="169">
                                <li class="" data-reactid="170">
                                    <a href="#" data-reactid="171">
                                        About
                                    </a>
                                </li>
                                <li class="" data-reactid="172">
                                    <a href="#" data-reactid="173">
                                        Help
                                    </a>
                                </li>
                                <li class="" data-reactid="174">
                                    <a href="#" data-reactid="175">
                                        Explore
                                    </a>
                                </li>
                                <li class="" data-reactid="176">
                                    <a href="#" data-reactid="177">
                                        Editor
                                    </a>
                                </li>
                                <li class="" data-reactid="178">
                                    <a href="#" data-reactid="179">
                                        Blog
                                    </a>
                                </li>
                                <li class="" data-reactid="180">
                                    <a href="#" data-reactid="181">
                                        Pricing
                                    </a>
                                </li>
                                <li class="" data-reactid="182">
                                    <a href="#" data-reactid="183">
                                        Contact
                                    </a>
                                </li>
                                <li class="footer-copyright" data-reactid="184">
                                    <a href="#" data-reactid="185">
                                        © GitBook.com
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- react-empty: 186 -->
            </div>
        </div>
        <div class="LoadingBar" data-reactid="187">
            <div class="bar" style="width:0%;display:none;" data-reactid="188">
                <div class="LoadingBar-shadow" style="display:none;" data-reactid="189">
                </div>
            </div>
        </div>
    </div>
</div>
</body>

</html>