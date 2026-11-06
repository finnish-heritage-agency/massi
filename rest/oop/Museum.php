<?php

class Museum extends Auth {

    private $moduleName;
    private $moduleId;
    private $endPoint;
    private $type; //Search, put, pull and so one...
    private $searchText;
    private $response;
    private $error;
    private $file; //If sending file to M+
    private $parameter; //If xml needs one parameter
    private $disableId;

    public function __construct($module = "Object", $tempFile = "museum") {
        $this->moduleName = $module;
        parent::__construct($tempFile);
    }

    function getEndPoint() {
        return $this->endPoint;
    }

    /**
     *
     * @return ucfirst string
     */
    function getModuleName() {
        return ucfirst($this->moduleName);
    }

    function getModuleId() {
        return $this->moduleId;
    }

    function getError() {
        return $this->error;
    }

    function getType() {
        return $this->type;
    }

    function getSearchText() {
        return $this->searchText;
    }

    /**
     * Käytetään search taulukon läpikäyntiin
     * @param boolean $value
     * @return array
     */
    function getResponse($value = true) {
        if ($value == true) { //Haetaan objectiId
            if (isset($this->response->modules)) {
                return (string) $this->response->modules->module->moduleItem->systemField->value;
            }
        } else {
            return $this->response;
        }
    }

    function getFile() {
        return $this->file;
    }

    function getParameter() {
        return $this->parameter;
    }

    function getDisableId() {
        return $this->disableId;
    }

    function setDisableId($disableId): void {
        $this->disableId = $disableId;
    }

    function setParameter($parameter): void {
        $this->parameter = $parameter;
    }

    function setModuleName($moduleName): void {
        $this->moduleName = $moduleName;
    }

    function setEndPoint($endPoint): void {
        $this->endPoint = $endPoint;
    }

    function setType($type): void {
        $this->type = strtolower($type);
    }

    function setSearchText($searchText): void {
        $this->searchText = $searchText;
    }

    function setResponse($response): void {
        $this->response = $response;
    }

    function setModuleId($moduleId): void {
        $this->moduleId = $moduleId;
    }

    function setError($error): void {
        $this->error = $error;
    }

    function setFile($file): void {
        $this->file = $file;
    }

    /**
     * Response all data to Response function
     * @param string $text
     */
    public function Search($text) {
        $this->setEndPoint("module/Object/search");
        $this->setType("search");
        $this->setSearchText($text);
        $response = $this->curlRequest();
        $this->setResponse($response);
        return $response;
    }

    /**
     *
     * @return array of ids wich has thumbnail = true
     */
    public function checkTrueThumbnailBoos() {
        $array = array();
        $this->setEndPoint("module/Object/" . $this->getModuleId());
        $this->setType("thumbnailboo");
        $response = $this->curlRequest();
        $this->setResponse($response);
        $message = $this->getResponse(false);

        $a = 0;
        while ($a < 10) {//Ei ole varma monesko paikka M+ssa
            $a++;
            if (isset($message->modules->module->moduleItem->moduleReference[$a]) && $message->modules->module->moduleItem->moduleReference[$a]->attributes()->name == "ObjMultimediaRef") {
                $attributes = $message->modules->module->moduleItem->moduleReference[$a];
                $a = 100;
            }
        }

        $array = [];
        if (!isset($attributes)) {
            return $array;
        }
        foreach ($attributes->moduleReferenceItem as $rivi) {
            if ($rivi->dataField->value == "true") {
                $id = $rivi->attributes()->moduleItemId;
                $array[] = (int) $id;
            }
        }
        return $array;
    }

    /**
     * Disable thumbnailboo status
     * @return 0 / 1
     */
    public function changeThumbnailBooStatus() {
        $this->checkParameters();
        if ($this->getDisableId() == "") {
            return -2;
        }
        $this->setEndPoint("module/Object/" . $this->getModuleId());
        $this->setType("disablethumbnailboo");
        $response = $this->curlRequest();
        $this->setResponse($response);
        $message = $this->getResponse(false);
        return $message;
    }

    public function sendFile() {
        $this->checkParameters();
        if (!file_exists($this->getFile())) {
            return "File not found: " . $this->getFile();
        }
        $this->setType("sendfile");
        $this->setEndPoint("module/Multimedia/" . $this->getModuleId() . "/attachment");
        $this->setResponse($this->curlRequest());
        if ($this->getResponse() != "" || $this->getError() != "") {
            $array_data = simplexml_load_string($this->getResponse(false));
            if (isset($array_data->body)) {
                $this->setError($array_data->body->h1);
                return -1;
            }
        }
        return 1;
    }

    public function changeFilename($xml) {
        $this->checkParameters();
        $this->setType("changefilename");
        $this->setEndPoint("module/Multimedia/" . $this->getModuleId());
        $this->setResponse($this->curlRequest($xml));
        return $this->getResponse(false);
    }

    public function deleteAttachment($file_id) {
        $this->checkParameters();
        $this->setType("deleteattachment");
        $this->setEndPoint("module/Multimedia/$file_id/attachment");
        $this->setResponse($this->curlRequest());
        return $this->getResponse(false);
    }

    /**
     * Lähetetään M+ järjestelmälle tiedostokohtaiset määritykset:
     * - Finna
     * - Tekijä
     * - Oikeus
     * - Oikeustyyppi
     * @return 1 / -1
     */
    public function fileDefinitions($xml) {
        $this->checkParameters();
        $this->setType("definitions");
        $this->setEndPoint("module/Multimedia/");
        $return = $this->curlRequest($xml);
        if (isset($return->body)) {
            $this->setError($return->body->h1);
            return $return;
        } else {
            $tmp = (string) $return->modules->module->moduleItem->systemField->value;
            if (is_numeric($tmp)) {
                return $tmp;
            } else {
                $this->setError("Ei saatu ID:tä");
                return -1;
            }
        }
    }

    private function checkParameters() {
        if ($this->getModuleId() == "" || !is_numeric($this->getModuleId())) {
            echo "ID on pakollinen";
            die();
        }
        if ($this->getModuleName() == "") {
            echo "Moduuli on pakollinen";
            die();
        }
        return null;
    }

    private function curlRequest($xml = false) {
        $array_data = null;
        $this->login(); //Make login and session key...
        if ($this->getSessionKey() != "" || !file_exists($this->getSessionKey())) {
            $this->login();
        }
        $additionalHeaders = "";
        $ch = curl_init($this->getBaseUrl() . "/ria-ws/application/" . $this->getEndPoint());
        curl_setopt($ch, CURLOPT_TIMEOUT, 3600);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);

        switch ($this->getType()) {
            case "thumbnailboo":
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_POST, 0);
                break;
            case "disablethumbnailboo":
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->disableThumbnailBoo());
                break;
            case "search":
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->makeSearchXml());
                break;
            case "definitions":
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                break;
            case "sendfile":
                $filename = basename($this->getFile());
                $additionalHeaders = "X-File-Name: $filename";
                $binary_file = file_get_contents($this->getFile());
                //$ch = curl_init($this->getBaseUrl() . "/ria-ws/application/" . $this->getEndPoint());
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/octet-stream ', $additionalHeaders));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, @$binary_file);
                break;
            case "changefilename":
                $ch = curl_init($this->getBaseUrl() . "/ria-ws/application/" . $this->getEndPoint());
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                break;
            case "deleteattachment":
                $ch = curl_init($this->getBaseUrl() . "/ria-ws/application/" . $this->getEndPoint());
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml', $additionalHeaders));
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
                break;
        }
        $auth = 'user[' . M_USERNAME . ']:session[' . file_get_contents($this->getSessionKey()) . ']';
        curl_setopt($ch, CURLOPT_USERPWD, $auth);
        $return = curl_exec($ch);
        if ($return != "") {
            curl_close($ch);
            if ($this->getType() == "changefilename" || $this->getType() == "deleteattachment") {
                return $return;
            }
            $array_data = @simplexml_load_string($return); //Ei oteta erroreita...

            if (isset($array_data->body)) {
                $this->setError($array_data->body->h1);
                $array_data = $return;
            }
        } else {
            if (!curl_errno($ch)) {
                $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if ($http_code >= 200 && $http_code <= 299) {
                    $array_data = 1;
                } elseif ($http_code >= 400 && $http_code <= 499) {
                    $this->setError("HTTPD ERROR $http_code");
                    $array_data = -1;
                }
            }
        }
        return $array_data;
    }

    /**
     *
     * @return part of curl message
     */
    private function makeSearchXml() {
        $msg = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $msg .= "<application xmlns=\"http://www.zetcom.com/ria/ws/module/search\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.zetcom.com/ria/ws/module/search http://docs.zetcom.com/ws/module/search/search_1_4.xsd\">\n";
        $msg .= "  <modules>\n";
        $msg .= "    <module name=\"" . $this->getModuleName() . "\">\n";
        $msg .= "      <search>\n";
        $msg .= "           <expert module=\"" . $this->getModuleName() . "\">\n";
        $msg .= "               <and>\n";
        $msg .= "                   <equalsField fieldPath=\"ObjObjectNumberVrt\" operand=\"" . $this->getSearchText() . "\"/>\n";
        $msg .= "               </and>\n";
        $msg .= "           </expert>\n";
        $msg .= "      </search>\n";
        $msg .= "    </module>\n";
        $msg .= "  </modules>\n";
        $msg .= "</application>";
        return $msg;
    }

    private function disableThumbnailBoo() {
        $msg = "<application xmlns='http://www.zetcom.com/ria/ws/module'>\n";
        $msg .= "<modules>\n";
        $msg .= "   <module name='Object'>\n";
        $msg .= "       <moduleItem id ='" . $this->getModuleId() . "'>\n";
        $msg .= "           <moduleReference name='ObjMultimediaRef'>\n";
        $msg .= "               <moduleReferenceItem moduleItemId='" . $this->getDisableId() . "'>\n";
        $msg .= "                   <dataField name='ThumbnailBoo'><value>false</value></dataField>\n";
        $msg .= "               </moduleReferenceItem>\n";
        $msg .= "           </moduleReference>\n";
        $msg .= "       </moduleItem>\n";
        $msg .= "   </module>\n";
        $msg .= "</modules>\n";
        $msg .= "</application>\n";

        return $msg;
    }
}
