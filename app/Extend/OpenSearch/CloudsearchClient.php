<?php
namespace App\Extend\OpenSearch;
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
 
/**
 * Cloudsearch service。
 *
 * 此类主要提供一下功能：
 * 1、根据请求的参数来生成签名和nonce。
 * 2、请求API服务并返回response结果。
 */
class CloudsearchClient {

  /**
   * 指定默认的请求方式；默认为GET.
   * @var string
   */
  const METHOD = 'GET';

  /**
   * 请求的方式，有GET和POST。
   * @var string
   */
  const METHOD_GET = 'GET';
  const METHOD_POST = 'POST';

  /**
   * 请求API的连接超时时间，单位为秒。
   * @var int
   */
  const CONNECT_TIMEOUT = 30;

  /**
   * 请求API的时间，单位为秒。
   * @var int
   */
  const TIMEOUT = 30;

  /**
   * 用户的client id。key_type为opensearch使用
   *
   * 此信息由网站中提供。
   *
   * @var string
   */
  protected $clientId;

  /**
   * 用户的秘钥。key_type为opensearch使用
   *
   * 此信息由网站中提供。
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * 用户阿里云网站中的accessKeyId,key_type为aliyun使用
   *
   * 此信息阿里云网站中提供
   *
   * @var string
   */
  protected $accessKeyId;

  /**
   * 用户阿里云accessKeyId对应的秘钥，key_type为aliyun使用
   *
   * 此信息阿里云网站中提供
   *
   */
  protected $secret;

  /**
   * 请求API的base URI.
   * @var string
   */
  protected $baseURI;

  /**
   * 当前API的版本号。
   * @var string
   */
  private $version = 'v2';

  /**
   * SDK的版本号
   * @var sttring
   */
  private $sdkVersion = 'v2.0.6';
  /**
   * 请求API的时间，单位为秒。
   * @var int
   */
  private $timeout = 10;
  /**
   * 请求API的连接超时时间，单位为秒。
   * @var int
   */
  private $connect_timeout = 1;

  /**
   * 请求的domain地址。
   * @var string
   */
  private $host = 'http://opensearch.aliyuncs.com';

  /**
   * 当前的请求方式，有socket和curl两种。
   * @var string
   */
  private $connect = 'socket';

  /**
   * 是否打开gzip功能。
   *
   * 如果打开gzip功能，则会在请求头中加上Accept-Encoding:gzip信息，同时如果服务器也设置了
   * 此功能的话，则服务器会返回zip的数据，此类会拿到gzip数据然后解压缩得到真实的数据。
   *
   * 此功能是用服务器计算换取网络耗时，对整个latency会有所降低。
   *
   * @var boolean
   */
  private $gzip = false;

  /**
   * 是否开启debug信息。
   * @var boolean
   */
  private $debug = false;
  /**
   * debug信息，当$debug = true时 存储sdk调用时产生的debug信息，供 getRequest 调用
   * @var string
   */
  private $debugInfo = "";

  /**
   *指定使用加密key和对应的secret
   *@var enum('opensearch','aliyun')
   */
  protected $key_type = 'opensearch';

   /**
   *指定阿里云签名算法方式
   *@var enum('HMAC-SHA1'）
   */
  protected $signatureMethod = 'HMAC-SHA1';

   /**
   *指定阿里云签名算法版本
   *@var enum('HMAC-SHA1'）
   */
  protected $signatureVersion = '1.0';

  /**
   * 构造函数
   *
   * 与服务器交互的客户端，支持单例方式调用
   *
   * @param string $key    用户的key，从阿里云网站中获取的Access Key ID。
   * @param string $secret 用户的secret，对应的Access Key Secret。
   * @param array $opts   包含下面一些可选信息
   * @subparam string version 使用的API版本。 默认值为:v2
   * @subparam string host    指定请求的host地址。默认值为:http://opensearch-cn-hangzhou.aliyuncs.com
   * @subparam string gzip    指定返回的结果用gzip压缩。 默认值为:false
   * @subparam string debug   打印debug信息。 默认值为:false
   * @subparam string signatureMethod  签名方式，目前支持HMAC-SHA1。 默认值为:HMAC-SHA1
   * @subparam string signatureVersion 签名算法版本。 默认值为:1.0
   * @param string $key_type key和secret类型，在这里必须设定为'aliyun'，表示这个是aliyun颁发的，默认值opensearch是为了兼容老用户。默认值为:opensearch
   */
  public function __construct($key, $secret, $opts = array(),$key_type = 'opensearch') {

    $this->key_type = $key_type;

    if ($this->key_type == 'opensearch'){
       $this->clientId = $key;
       $this->clientSecret = $secret;
    } elseif ($this->key_type == 'aliyun'){
       $this->accessKeyId = $key;
       $this->secret = $secret;
    } else {
       $this->key_type = 'opensearch';
       $this->clientId = $key;
       $this->clientSecret = $secret;
    }
    if (isset($opts['host']) && !empty($opts['host'])) {
      //对于用户通过参数指定的host，需要检查host结尾是否有/，有则去掉
      if(substr($opts['host'], -1) == "/"){
        $this->host = trim($opts['host'], '/');
      }else{
        $this->host = $opts['host'];
      }
    }

    if (isset($opts['version']) && !empty($opts['version'])) {
      $this->version = $opts['version'];
    }

    if (isset($opts['timeout']) && !empty($opts['timeout'])) {
      $this->timeout= $opts['timeout'];
    }

    if (isset($opts['connect_timeout']) && !empty($opts['connect_timeout'])) {
      $this->connect_timeout= $opts['connect_timeout'];
    }

    if (isset($opts['gzip']) && $opts['gzip'] == true) {
      $this->gzip = true;
    }

    if (isset($opts['debug']) && $opts['debug'] == true) {
      $this->debug = true;
    }

    if (isset($opts['signatureMethod']) && !empty($opts['signatureMethod'])) {
      $this->signatureMethod = $opts['signatureMethod'];
    }

     if (isset($opts['signatureVersion']) && !empty($opts['signatureVersion'])) {
      $this->signatureVersion = $opts['signatureVersion'];
    }

     $this->baseURI = rtrim($this->host, '/');

 }


  /**
   * 请求服务器
   *
   * 向服务器发出请求并获得返回结果。
   *
   * @param string $path   当前请求的path路径。
   * @param array $params 当前请求的所有参数数组。
   * @param string $method 当前请求的方法。默认值为:GET
   * @return string 返回获取的结果。
   * @donotgeneratedoc
   */
  public function call($path, $params = array(), $method = self::METHOD) {
    $url = $this->baseURI . $path;
    if ($this->key_type == 'opensearch') {
      $params['client_id'] = $this->clientId;
      $params['nonce'] = $this->_nonce();
      $params['sign'] = $this->_sign($params);
    } else {
      $params['Version'] = $this->version;
      $params['AccessKeyId'] = $this->accessKeyId;
      $params['SignatureMethod']=$this->signatureMethod;
      $params['SignatureVersion']=$this->signatureVersion;
      $params['SignatureNonce'] = $this->_nonce_aliyun();
      $params['Timestamp'] = gmdate('Y-m-d\TH:i:s\Z');
      $params['Signature'] = $this->_sign_aliyun($params,$method);
    }
    if ($this->connect == 'curl') {
      $result = $this->_curl($url, $params, $method);
    } else {
      $result = $this->_socket($url, $params, $method);
    }
    return $result;
  }

  /**
   * 生成当前的nonce值。
   *
   * NOTE: $time为10位的unix时间戳。
   *
   * @return string 返回生成的nonce串。
   */
  protected function _nonce() {
    $time = time();
    return md5($this->clientId . $this->clientSecret . $time) . '.' . $time;
  }

  /**
   * 生产当前的aliyun签名方式对应的nonce值
   *
   * NOTE：这个值要保证访问唯一性，建议用如下算法，商家也可以自己设置一个唯一值
   *
   * @return string  返回生产的nonce串
   */
  protected function _nonce_aliyun() {
     $microtime = $this->get_microtime();
     return $microtime . mt_rand(1000,9999);
  }

  /**
   * 根据参数生成当前的签名。
   *
   * 如果指定了sign_mode且sign_mode为1，则参数中的items将不会被计算签名。
   *
   * @param array $params 返回生成的签名。
   * @return string
   */
  protected function _sign($params = array()) {
    $query = "";
    if (isset($params['sign_mode']) && $params['sign_mode'] == 1) {
      unset($params['items']);
    }
    if (is_array($params) && !empty($params)) {
      ksort($params);
      $query = $this->_buildQuery($params);
    }
    return md5($query . $this->clientSecret);
  }

  /**
   * 根据参数生成当前得签名
   *
   * 如果指定了sign_mode且sign_mode为1，则参数中的items将不会被计算签名
   *
   * @param array $params 返回生成签名
   * @return string
   */
  protected function _sign_aliyun($params = array(),$method=self::METHOD){
    if (isset($params['sign_mode']) && $params['sign_mode'] == 1) {
      unset($params['items']);
    }
    $params = $this->_params_filter($params);
    $query = '';
    $arg = '';
    if(is_array($params) && !empty($params)){
      while (list ($key, $val) = each ($params)) {
        $arg .= $this->_percentEncode($key) . "=" . $this->_percentEncode($val) . "&";
      }
      $query = substr($arg, 0, count($arg) - 2);
    }
    $base_string = strtoupper($method).'&%2F&' .$this->_percentEncode($query);
    return base64_encode(hash_hmac('sha1', $base_string, $this->secret."&", true));
  }

  /**
   * 过滤阿里云签名中不用来签名的参数,并且排序
   *
   * @param array $params
   * @return array
   *
   */
  protected function _params_filter($parameters = array()){
    $params = array();
    while (list ($key, $val) = each ($parameters)) {
      if ($key == "Signature" ||$val === "" || $val === NULL){
        continue;
      } else {
        $params[$key] = $parameters[$key];
      }
    }
    ksort($params);
    reset($params);
    return $params;
  }

  protected function _percentEncode($str)
  {
          // 使用urlencode编码后，将"+","*","%7E"做替换即满足 API规定的编码规范
          $res = urlencode($str);
          $res = preg_replace('/\+/', '%20', $res);
          $res = preg_replace('/\*/', '%2A', $res);
          $res = preg_replace('/%7E/', '~', $res);
          return $res;
  }
  /**
   * 通过curl的方式获取请求结果。
   * @param string $url 请求的URI。
   * @param array $params 请求的参数数组。
   * @param string $method 请求的方法，默认为self::METHOD。
   * @return string 返回获取的结果。
   */
  private function _curl($url, $params = array(), $method = self::METHOD) {
    $query = $this->_buildQuery($params);
    $method = strtoupper($method);

    if ($method == self::METHOD_GET) {
      $url .= preg_match('/\?/i', $url) ? '&' . $query : '?' . $query;
    } else {
      $method = self::METHOD_POST;
    }

    $options = array(
      CURLOPT_HTTP_VERSION => 'CURL_HTTP_VERSION_1_1',
      CURLOPT_CONNECTTIMEOUT => $this->connect_timeout,
      CURLOPT_TIMEOUT => $this->timeout,
      CURLOPT_CUSTOMREQUEST => $method,
      CURLOPT_HEADER => false,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_USERAGENT => "opensearch/php sdk ".$this->sdkVersion,//php sdk 版本信息
      CURLOPT_HTTPHEADER => array('Expect:')
    );

    if ($method == self::METHOD_POST) {
      $options[CURLOPT_POSTFIELDS] = $params;
    }

    if ($this->gzip) {
      $options[CURLOPT_ENCODING] = 'gzip';
    }

    $session = curl_init($url);
    curl_setopt_array($session, $options);
    $response = curl_exec($session);
    $info = curl_getinfo($session);

    if($this->debug){
      $this->debugInfo = $info;//query基本信息，供调试使用
    }

    curl_close($session);

    return $response;
  }


  /**
   * 通过socket的方式获取请求结果。
   * @param string $url 请求的URI。
   * @param array $params 请求的参数数组。
   * @param string $method 请求方法，默认为self::METHOD。
   * @throws Exception
   * @return string
   */
  private function _socket($url, $params = array(), $method = self::METHOD) {
    $method = strtoupper($method);

    $parse = $this->_parseUrl($url);
    $content = $this->_buildRequestContent(
        $parse,
        $method,
        http_build_query($params, '', '&')
    );
    if ($this->debug) {
      $this->debugInfo = $content;
    }

    $socket = fsockopen(
        $parse['host'],
        $parse["port"],
        $errno,
        $errstr,
        $this->connect_timeout
    );

    stream_set_timeout($socket, $this->timeout);

    if (!$socket) {
      throw new Exception("Connect " . $parse['host'] . ' fail.');
    }

    $response = '';
    fwrite($socket, $content);
    while($data = fgets($socket)) {
      $response .= $data;
    }
    fclose($socket);

    $ret = $this->_parseResponse($response);
    return $ret['result'];
  }

  /**
   * 调试接口
   *
   * 获取SDK调用的调试信息,需要指定debug=true才能使用
   *
   * @return array\null 调试开关(debug)打开时返回调试信息。
   */
  public function getRequest(){
    if ($this->debug) {
      return $this->debugInfo;
    } else {
      return null;
    }
  }

  /**
   * 解析http返回的结果，并分析出response 头和body。
   * @param string $response_text
   * @return array
   */
  private function _parseResponse($response) {
    list($headerContent, ) = explode("\r\n\r\n", $response);
    $header = $this->_parseHttpSocketHeader($headerContent);
    $response = trim(stristr($response, "\r\n\r\n"), "\r\n");

    $ret = array();
    $ret["result"] =
        (isset($header['Content-Encoding']) &&
         trim($header['Content-Encoding']) == 'gzip') ?
        $this->_gzdecode($response, $header) : $this->_checkChunk($response, $header);
    $ret["info"]["http_code"] =
        isset($header["http_code"]) ? $header["http_code"] : 0;
    $ret["info"]["headers"] = $header;

    return $ret;
  }


  /**
   * 生成http头信息。
   *
   * @param array $parse
   * @param string $method HTTP方法。
   * @param string $data HTTP参数串。
   * @return string
   */
  private function _buildRequestContent(&$parse, $method, $data) {
    $strLength = '';
    $content = '';

    if ($method == self::METHOD_GET) {
      $data = ltrim($data, '&');
      $query = isset($parse['query']) ? $parse['query'] : '';
      $parse['path'] .= ($query ? '&' : '?') . $data;
    } else {
      $method = self::METHOD_POST;
      $strLength = "Content-length: " . strlen($data) . "\r\n";
      $content = $data;
    }

    $write = $method . " " . $parse['path'] . " HTTP/1.0\r\n";
    $write .= "Host: " . $parse['host'] . "\r\n";
    $write .= "Content-type: application/x-www-form-urlencoded\r\n";
    $write .= "User-Agent: opensearch/php sdk ".$this->sdkVersion."\r\n";
    if ($this->gzip) {
      $write .= "Accept-Encoding: gzip\r\n";
    }
    $write .= $strLength;
    $write .= "Connection: close\r\n\r\n";
    $write .= $content;

    return $write;
  }


  /**
   * 把数组生成http请求需要的参数。
   * @param array $params
   * @return string
   */
  private function _buildQuery($params) {
    $args = http_build_query($params, '', '&');
    // remove the php special encoding of parameters
    // see http://www.php.net/manual/en/function.http-build-query.php#78603
    //return preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $args);
    return $args;
  }


  /**
   * 解析URL并生成host、schema、path、query等信息。
   * @param string $url
   * @throws Exception
   * @return Ambigous <string, mixed>
   */
  private function _parseUrl($url) {
    $parse = parse_url($url);
    if (empty($parse) || !is_array($parse)) {
      throw new Exception("Host is empty.");
    }

    if (!isset($parse['port']) || !$parse['port']) {
      $parse['port'] = '80';
    }

    $parse['host'] = str_replace(
        array('http://', 'https://'),
        array('', 'ssl://'),
        $parse['scheme'] . "://"
    ) . $parse['host'];

    $parse["path"] = isset($parse["path"]) ? $parse["path"] : '/';
    $query = isset($parse['query']) ? $parse['query'] : '';

    $path = str_replace(array('\\', '//'), '/', $parse['path']);
    $parse['path'] = $query ? $path . "?" . $query : $path;

    return $parse;
  }

  /**
   * 解析返回的header头。
   * @param string $str 头信息。
   * @return array 返回头信息的数组。
   */
  private static function _parseHttpSocketHeader($str) {
    $slice = explode("\r\n", $str);
    $headers = array();

    foreach ($slice as $v) {
      if (false !== strpos($v, "HTTP")) {
        list(, $headers["http_code"]) = explode(" ", $v);
        $headers["status"] = $v;
      } else {
        $item = explode(":", $v);
        $headers[$item[0]] = isset($item[1]) ? $item[1] : '';
      }
    }

    return $headers;
  }


  /**
   * 解压缩gzip生成的数据。
   *
   * @param string $data 压缩的数据。
   * @return string 解压缩的数据。
   */
  private static function _gzdecode($data, $header, $rn = "\r\n") {
    if (isset($header['Transfer-Encoding'])){
      $lrn = strlen($rn);
      $str = '';
      $ofs = 0;
      do {
        $p = strpos($data, $rn, $ofs);
        $len = hexdec(substr($data, $ofs, $p - $ofs));
        $str .= substr($data, $p + $lrn, $len);
        $ofs = $p + $lrn * 2 + $len;
      } while ($data[$ofs] !== '0');
      $data = $str;
    }
    if (isset($header['Content-Encoding'])) {
      $data = gzinflate(substr($data, 10));
    }
    return $data;
  }

  /**
   * 检查当前是否是返回chunk，如果是的话，从body中获取content长度并截取。
   *
   * @param string $data body内容。
   * @param array $header header头信息的数组。
   * @param string $rn chunk的截取字符串。
   *
   * @return string 如果为chunk则返回正确的body内容，否则全部返回。
   */
  private static function _checkChunk($data, $header, $rn = "\r\n") {
    if (isset($header['Transfer-Encoding'])) {
      $lrn = strlen($rn);
      $p = strpos($data, $rn, 0);
      $len = hexdec(substr($data, 0, $p));
      $data = substr($data, $p + 2, $len);
    }
    return $data;
  }

 protected function get_microtime() {
    list($usec, $sec) = explode(" ", microtime());
    return floor(((float)$usec + (float)$sec) * 1000);
 }

}
