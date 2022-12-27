<?php

/** http://docs.zetcom.com/ws/#Security_and_authentication

 */
class Auth {

    private $sessionKey;
    private $baseUrl;
    private $sessionEndpoint;
    private $tempFile;
    private $loginStatus;
    private $loginText;

    public function __construct($tempFile = "museum") {
        $this->baseUrl = M_URL;
        $this->setTempFile($tempFile);
    }

    function getBaseUrl() {
        return $this->baseUrl;
    }

    function getSessionEndpoint() {
        return $this->sessionEndpoint;
    }

    function getSessionKey() {
        return $this->sessionKey;
    }

    function getTempFile() {
        return $this->tempFile;
    }

    function getLoginStatus() {
        return $this->loginStatus;
    }

    function getLoginText() {
        return $this->loginText;
    }

    function setLoginText($loginText): void {
        $this->loginText = $loginText;
    }

    function setLoginStatus($loginStatus): void {
        $this->loginStatus = $loginStatus;
    }

    function setTempFile($tempFile): void {
        $tmp = str_replace("/", "", $tempFile);
        $tmp = str_replace(".", "", $tmp);
        $tmp = str_replace(" ", "", $tmp);
        $tmp = str_replace("\\", "", $tmp);
        if (!ctype_alpha($tmp)) {
            $this->tempFile = "museum.key";
        } else {
            $this->tempFile = $tmp . ".key";
        }
    }

    function setSessionKey($sessionKey): void {
        $this->sessionKey = $sessionKey;
    }

    function setBaseUrl($baseUrl): void {
        $this->baseUrl = $baseUrl;
    }

    function setSessionEndpoint($sessionEndpoint): void {
        $this->sessionEndpoint = $sessionEndpoint;
    }

    /**
     * This function is using username and password compination and after successed login it will store the login credentials to sessionfile.
     * @return int
     *  -3 Site is broken
     *  -2
     *  -1 Wrong password
     *   1 OK
     *   2 SESSION Login
     */
    public function login($reset = false) {
        $additionalHeaders = "";
        $cache_file = TMP . $this->getTempFile();
        $filemtime = @filemtime($cache_file);  // returns FALSE if file does not exist
        if (!$filemtime or (time() - $filemtime >= CACHE_LIFETIME)) {
            @unlink(TMP . $this->getTempFile());
        }

        if (file_exists(TMP . $this->getTempFile())) {
            shell_exec("touch " . TMP . $this->getTempFile()); // annetaan lisäaikaa
            $login_text = "Logged by session";
            $endPoint = "/ria-ws/application/module/Object/1";
        } else {
            $login_text = "Logged by username & password";
            $endPoint = "/ria-ws/application/session";
        }
        $ch = curl_init($this->getBaseUrl() . $endPoint);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($this->getSessionKey() != "" && file_exists(TMP . $this->getTempFile()) && file_get_contents($this->getSessionKey()) != "") {
            $this->setSessionKey(TMP . $this->getTempFile());
            $auth = 'user[' . M_USERNAME . ']:session[' . file_get_contents($this->getSessionKey()) . ']';
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        } else {
            curl_setopt($ch, CURLOPT_USERPWD, "user[" . M_USERNAME . "]:password[" . M_PASSWORD . "]");
        }

        $return = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            if (file_exists($this->getSessionKey())) {
                unlink($this->getSessionKey());
            }
            if ($reset == false) {
                $this->login(true); //Session is ended, lets make another login.. Only once...
            }
            $text = "AUTH ERROR -1: M+ Error: " . print_r($error, true);
            $this->setLoginText($text);
            writeToLog($text);
            $status = -1;
        } else {
            if (strpos($return, "Invalid") !== false) { //Wrong password
                if (file_exists($this->getSessionKey())) {
                    unlink($this->getSessionKey());
                }
                if ($reset == false) {
                    $this->login(true); //Session is ended, lets make another login.. Only once...
                }
                $text = "AUTH ERROR -2: Invalid credentials";
                $this->setLoginText($text);
                writeToLog($text);
                $status = -2;
            } elseif (strpos($return, "500") !== false) { //Site is broken...
                $status = -3;
            } else {
                $authXml = simplexml_load_string($return);
            }
            if (isset($authXml->session->key)) { //First accepted login
                $key = (string) $authXml->session->key; // avain talteen
                if ($key != "") {
                    file_put_contents(TMP . $this->getTempFile(), $key);
                }
                $status = 1;
                $text = "OK: Logged in MuseomPLUS: $login_text ($status)";
                $this->setLoginText($text);
                writeToLog($text);
            } elseif (isset($authXml->modules->module->moduleItem)) { //Sessiokirjautuminen
                $status = 2;
                $this->setSessionKey(TMP . $this->getTempFile());
                $text = "OK: Logged in MuseomPLUS: $login_text ($status)";
                $this->setLoginText($text);
                writeToLog($text);
            }

            $this->setLoginStatus($status);
        }
        return $status;
    }

}
