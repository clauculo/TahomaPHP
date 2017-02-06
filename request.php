<?php

class Request {

    /**
     * @var string $url
     */
    private $url = '';
    private $domain = '';
    private $postData = '';
    private $ckfile = '';
    private $createCookieJar = false;

    /**
     * tahomeRequest constructor.
     *
     * @param string $url starting from tld
     * @example /enduser-mobile-web/enduserAPI/login
     */
    public function __construct($url, $id, $password, $ckfile='') {
        $this->url = $url;
        $this->domain = 'https://www.tahomalink.com';
        $this->postData = "userId=$id&userPassword=$password";
        $this->ckfile = $ckfile;
        $this->createCookieJar = false;
    }

    public function execute () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->domain . $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->postData);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        if ($this->createCookieJar) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->ckfile);
        } else {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->ckfile);
        }

        $output = curl_exec($ch);
        curl_close($ch);

        if ($output == "") {
            echo $this->url . ": Invalid return\n";
        }
        return $output;
    }

    public function setPostData($postData) {
        $this->postData = $postData;
    }

    public function createCookieFile() {
        $ckfile = tempnam("/tmp", "CURLCOOKIE");
        $this->createCookieJar = true;
        $this->ckfile = $ckfile;
        return $ckfile;
    }
}