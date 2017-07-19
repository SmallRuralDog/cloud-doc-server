<?php namespace zgldh\QiniuStorage;

use JsonSerializable;

class QiniuUrl implements JsonSerializable
{
    private $url = null;
    private $parameters = [];

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function __toString()
    {
        $url = trim($this->getUrl(), '?&');

        $parameters = $this->getParameters();
        $parameterString = join('&', $parameters);

        if ($parameterString) {
            if (strrpos($url, '?') === false) {
                $url .= '?' . $parameterString;
            } else {
                $url .= '&' . $parameterString;
            }
        }
        if (is_string($url) === false) {
            return '';
        }
        return $url;
    }

    /**
     * @return null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param null $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return null
     */
    public function getDownload()
    {
        return $this->getParameter('download');
    }

    /**
     * @param null $download
     */
    public function setDownload($download)
    {
        return $this->setParameter('download', urlencode($download));
    }

    /**
     * @return array
     */
    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameter($name, $value)
    {
        $this->parameters[$name] = $name . '/' . $value;
        return $this;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    function jsonSerialize()
    {
        return $this->__toString();
    }
}
