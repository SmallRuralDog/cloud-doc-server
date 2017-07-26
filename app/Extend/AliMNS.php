<?php

namespace App\Extend;

use AliyunMNS\Client;
use AliyunMNS\Exception\MnsException;
use AliyunMNS\Model\SubscriptionAttributes;
use AliyunMNS\Requests\CreateTopicRequest;
use AliyunMNS\Requests\PublishMessageRequest;
use SimpleXMLElement;

class AliMNS
{
    private $accessId;
    private $accessKey;
    private $endPoint;
    private $client;

    public function __construct($accessId = '', $accessKey = '', $endPoint = '')
    {
        $this->accessId = "GOkscSXVTLkhIenG";
        $this->accessKey = "OnumvS4eeijYaMlEZLok48ISMvStc9";
        $this->endPoint = "http://1351835371264027.mns.cn-hangzhou-internal-vpc.aliyuncs.com";
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
    }

    /**
     * @param $topicName string 主题名称
     * @param $subscriptionName string  订阅名称   一个主题可以创建多个订阅
     * @param $url string  订阅通知地址  只支持一级目录   比如 xxx.com/get_message  所以这个地址需要添加路由
     * @return bool
     */
    public function create($topicName, $subscriptionName, $url)
    {
        $this->client = new Client($this->endPoint, $this->accessId, $this->accessKey);
        // 1. create topic
        $request = new CreateTopicRequest($topicName);
        try {
            $res = $this->client->createTopic($request);
        } catch (MnsException $e) {
            return false;
        }
        $topic = $this->client->getTopicRef($topicName);
        $attributes = new SubscriptionAttributes($subscriptionName, $url);
        try {
            $topic->subscribe($attributes);
            return true;
        } catch (MnsException $e) {
            return false;
        }
    }


    /**
     * 发送消息
     * @param $topicName string  主题名称
     * @param $messageBody string  发送数据  字符串类型
     * @return bool
     */
    public function send_message($topicName, $messageBody)
    {
        $topic = $this->client->getTopicRef($topicName);
        // as the messageBody will be automatically encoded
        // the MD5 is calculated for the encoded body
        $bodyMD5 = md5(base64_encode($messageBody));
        $request = new PublishMessageRequest($messageBody);
        try {
            $res = $topic->publishMessage($request);
            return true;
        } catch (MnsException $e) {
            return false;
        }
    }

    /**
     * 获取消息
     * @return SimpleXMLElement
     */
    public function get_message()
    {
        $headers = $this->getallheaders();
        foreach ($headers as $key => $value) {
            if (0 === strpos($key, 'x-mns-')) {
                $tmpHeaders[$key] = $value;
            }
        }
        ksort($tmpHeaders);
        $canonicalizedMNSHeaders = implode("\n", array_map(function ($v, $k) {
            return $k . ":" . $v;
        }, $tmpHeaders, array_keys($tmpHeaders)));
        $method = $_SERVER['REQUEST_METHOD'];
        $canonicalizedResource = $_SERVER['REQUEST_URI'];
        $contentMd5 = '';
        if (array_key_exists('Content-MD5', $headers)) {
            $contentMd5 = $headers['Content-MD5'];
        } else if (array_key_exists('Content-md5', $headers)) {
            $contentMd5 = $headers['Content-md5'];
        } else if (array_key_exists('Content-Md5', $headers)) {
            $contentMd5 = $headers['Content-Md5'];
        } else if (array_key_exists('content-md5', $headers)) {
            $contentMd5 = $headers['content-md5'];
        }

        $contentType = '';
        if (array_key_exists('Content-Type', $headers)) {
            $contentType = $headers['Content-Type'];
        }
        $date = $headers['Date'];
        $stringToSign = strtoupper($method) . "\n" . $contentMd5 . "\n" . $contentType . "\n" . $date . "\n" . $canonicalizedMNSHeaders . "\n" . $canonicalizedResource;
        $publicKeyURL = base64_decode($headers['x-mns-signing-cert-url']);
        $publicKey = $this->get_by_url($publicKeyURL);
        $signature = $headers['Authorization'];
        $pass = $this->verify($stringToSign, $signature, $publicKey);
        /*if (!$pass)
        {
            dump($rObj->messagePrivatePublish("lyf_6", 1, "RC:TxtMsg", '{"content":"验证失败"}'));
            return;
        }*/
        $content = file_get_contents("php://input");
        if (empty($content)) {
            http_response_code(401);
            log("get_tkl", "内容为空");
            exit();
        }
        if (!empty($contentMd5) && $contentMd5 != base64_encode(md5($content))) {
            http_response_code(401);
            log("get_tkl", "验证失败");
            exit();
        }
        $msg = new SimpleXMLElement($content);
        return $msg;
    }

    //将XML转为array
    function xmlToArray($xml)
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }

    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
            }
        }
        return $headers;
    }

    function get_by_url($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $output = curl_exec($ch);

        curl_close($ch);

        return $output;
    }

    function verify($data, $signature, $pubKey)
    {
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($signature), $res);
        openssl_free_key($res);

        return $result;
    }
}