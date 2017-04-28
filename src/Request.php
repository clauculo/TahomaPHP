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
    private $isJasonPostData = false;

    /**
     * tahomeRequest constructor.
     *
     * @param string $url string
     * @param string $userId normally an e-mailaddress
     * @param string $password
     * @param string $ckfile
     *
     * @example /enduser-mobile-web/enduserAPI/login
     */
    public function __construct($url, $userId, $password, $ckfile='') {
        $this->url = $url;
        $this->domain = 'https://www.tahomalink.com';
        $this->postData = "userId=$userId&userPassword=$password";
        $this->ckfile = $ckfile;
    }

    /**
     * @return mixed
     */
    public function execute () {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->domain . $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if ($this->isJasonPostData) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
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

    /**
     * Method to set data for POST
     *
     * @param array $postData
     *
     * @return void
     */
    public function setPostData($postData) {
        $this->postData = $postData;
    }

    /**
     * Method to set data for POST (in Json Format)
     *
     * @param string $postData Json format
     *
     * @return void
     */
    public function setJasonPostData($postData) {
        $this->postData = $postData;
        $this->isJasonPostData = true;
    }

    /**
     * Method to create cookieFile
     *
     * @return string name of tmp file
     */
    public function createCookieFile() {
        $ckfile = tempnam("/tmp", "CURLCOOKIE");
        $this->createCookieJar = true;
        $this->ckfile = $ckfile;
        return $ckfile;
    }
}