<?php

/**
 * Class TahomaController
 *
 * A class to send commands to the Somfy Tahoma API
 * It is now possible to create simple web-application to manage your Home automation.
 *
 * @author Wouter Post <wouteris@hotmail.com>
 * @package tahoma
 */
class TahomaController {

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $password;

    /**
     * User is (normally an e-mailaddress)
     *
     * @param string $userId
     */
    public function setUserId($userId) {
        $this->userId = $userId;
    }

    /**
     * Sorry we need to pass the password unprotected over the line.
     *
     * @param $password
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * List of configurable objects, here you can debug the interfaces of your Somfy devices.
     *
     * @return mixed json object on success
     */
    public function getDevices() {
        $url = '/enduser-mobile-web/enduserAPI/login';
        $login = new Request($url, $this->userId, $this->password);
        $ckfile = $login->createCookieFile();
        $login->execute();

        $url = '/enduser-mobile-web/externalAPI/refreshAllStates';
        $refresh = new Request($url, $this->userId, $this->password, $ckfile);
        $refresh->execute();

        $url = '/enduser-mobile-web/externalAPI/json/getSetup?_=1434999539745';
        $setup = new Request($url, $this->userId, $this->password, $ckfile);
        $output = $setup->execute();

        $tahoma = json_decode($output);
        return $tahoma->setup->devices;
    }

    /**
     * @return mixed
     */
    public function getScenarios() {
        $url = '/enduser-mobile-web/enduserAPI/login';
        $login = new Request($url, $this->userId, $this->password);
        $ckfile = $login->createCookieFile();
        $login->execute();


        $url = "/enduser-mobile-web/externalAPI/json/getActionGroups";
        $actionGroups = new Request($url, $this->userId, $this->password, $ckfile);
        $output = $actionGroups->execute();
        if ($output == "") {
            echo "Invalid return";
        }
        $tahoma = json_decode($output);
        return $tahoma->actionGroups;
    }

    /**
     * @param $execId
     * @return string
     */
    public function cancelExecutions($execId) {
        $url = "https://www.tahomalink.com/enduser-mobile-web/enduserAPI/login";
        $postData = "userId=$this->userId&userPassword=$this->password";
        $ckfile = tempnam("/tmp", "CURLCOOKIE");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $ckfile);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output == "") {
            echo "Invalid return";
        }
        $url = "https://www.tahomalink.com/enduser-mobile-web/externalAPI/json/cancelExecutions";
        //	$url = "https://www.tahomalink.com/enduser-mobile-web/enduserAPI//exec/cancelExecutions";
        log::add('tahoma', 'debug', "cancelExecutions: (" . $execId . ")");
        $postData = ['execId' => $execId];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        $output = curl_exec($ch);
        curl_close($ch);
        log::add('tahoma', 'debug', "return http cancelExecutions: (" . $output . ")");
        if ($output == "") {
            echo "Invalid return";
            return "";
        }
        return "";
    }

    /**
     * Method to send commands to Tahoma api to handle action.
     *
     * @param $deviceURL
     * @param $commandName
     * @param $parameters
     * @param string $equipmentName
     * @return string
     */
    public function sendCommand($deviceURL, $commandName, $parameters, $equipmentName = "Equipment") {
        $url = '/enduser-mobile-web/enduserAPI/login';
        $login = new Request($url, $this->userId, $this->password);
        $ckfile = $login->createCookieFile();
        $login->execute();

        $command["name"] = $commandName;
        if ($parameters != "") {
            $command["parameters"] = $parameters; // array(100);
        }
        $action["commands"][] = $command;
        $action["deviceURL"] = $deviceURL;
        $row["label"] = $equipmentName;
        $row["actions"][] = $action;

        $url = '/enduser-mobile-web/enduserAPI/exec/apply';
        $refresh = new Request($url, $this->userId, $this->password, $ckfile);
        $refresh->setJasonPostData(json_encode($row));
        $output = $refresh->execute();

        $outputJson = json_decode($output);
        return $outputJson->execId;
    }

    /**
     * Method to execute an action
     *
     * @param string $oid
     * @param int $delay
     *
     * @return string
     */
    public function execAction($oid, $delay = 0) {
        $url = '/enduser-mobile-web/enduserAPI/login';
        $login = new Request($url, $this->userId, $this->password);
        $ckfile = $login->createCookieFile();
        $output = $login->execute();

        $url = sprintf("https://www.tahomalink.com/enduser-mobile-web/externalAPI/json/scheduleActionGroup?oid=%s&delay=%d", $oid, $delay);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $ckfile);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output == "") {
            echo "Invalid return";
            return "";
        }

        $outputJson = json_decode($output);
        return $outputJson->actionGroup;
    }
}