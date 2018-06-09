<?php

namespace NetBull\CoreBundle\Locale\Cookie;

use Symfony\Component\HttpFoundation\Cookie;

/**
 * Class LocaleCookie
 * @package NetBull\CoreBundle\Locale\Cookie
 */
class LocaleCookie
{
    private $name;
    private $ttl;
    private $path;
    private $domain;
    private $secure;
    private $httpOnly;
    private $setOnChange;

    /**
     * LocaleCookie constructor.
     * @param $name
     * @param $ttl
     * @param $path
     * @param null $domain
     * @param $secure
     * @param $httpOnly
     * @param $setOnChange
     */
    public function __construct($name, $ttl, $path, $domain = null, $secure, $httpOnly, $setOnChange)
    {
        $this->name = $name;
        $this->ttl = $ttl;
        $this->path = $path;
        $this->domain = $domain;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->setOnChange = $setOnChange;
    }

    /**
     * @param $locale
     * @return Cookie
     */
    public function getLocaleCookie($locale)
    {
        $value = $locale;
        $expire = $this->computeExpireTime();
        $cookie = new Cookie($this->name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
        return $cookie;
    }

    /**
     * @return mixed
     */
    public function setCookieOnChange()
    {
        return $this->setOnChange;
    }

    /**
     * @return \DateTime
     */
    private function computeExpireTime()
    {
        $expireTime = time() + $this->ttl;
        $date = new \DateTime();
        $date->setTimestamp($expireTime);
        return $date;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
}
