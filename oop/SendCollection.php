<?php

/**
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class SendCollection {

    private $folder;
    private $kokoelmatunnus;
    private $data;
    private $cataloged;
    private $domain; // TO M+ service
    private $rowId;
    private $files;
    private $error;
    private $errorText;
    private $module;

    public function __construct($object = "", $cataloged = true, $domain = "", $row_id = 0) {
        $this->domain = $domain;
        $this->rowId = $row_id;
        $this->cataloged = $cataloged; //Käyttötapaus 1(true) TAI käyttötapaus 2(false)
        $this->setFolder(PICTURE_FOLDER . $object); //Static folder
        $this->setKokoelmatunnus($object);
    }

    /**
     * Tehdään tiedostokohtaiset määritykset ja rakennetaan näistä XML paketti M+ järjestelmälle
     * HUOM! Ajetaan kaikkien alustuksien jälkeen.
     */
    public function makeDefinitions() {
        if (file_exists($this->getFolder())) {
            $files = array();
            $tmp_files = array_slice(array_filter(scandir($this->getFolder())), 2);
            foreach ($tmp_files as $file) {
                if (is_dir($file)) {
                    continue;
                }
                $file_object = new ProcessingFile($this->getFolder(), $file);
                if (strtolower($file_object->getType()) == "image") {
                    $tmp = $this->getMetadata($file_object);
                    if (!isset($tmp["data"])) { //DB connection error, just ignore
                        /*
                          $this->setError(13);
                          $this->setErrorText("Ei saatu haetua dataa aineistolle");
                         *
                         */
                        return null;
                    }
                    $file_object->setData($tmp["data"]);
                    $file_object->setXml($tmp["xml"]); //XML tiedot M+ järjestelmään
                    $files[] = $file_object;
                } elseif ($file_object->getErrorText() != "") {
                    $this->setErrorText($file_object->getErrorText());
                    $this->setError(1);
                } else {
                    $this->setErrorText("Tiedostotyyppi ei ole IMAGE!");
                    $this->setError(1);
                }
            }
            $this->files = $files;
        }
    }

    function getDomain() {
        return $this->domain;
    }

    function getFolder() {
        return $this->folder;
    }

    function getKokoelmatunnus() {
        return $this->kokoelmatunnus;
    }

    function getData() {
        return $this->data;
    }

    /**
     * Listan rivi_id
     * @return int
     */
    function getRowId() {
        return $this->rowId;
    }

    function getFiles() {
        return $this->files;
    }

    function getError() {
        return $this->error;
    }

    function getErrorText() {
        return $this->errorText;
    }

    function getModule() {
        return $this->module;
    }

    function setModule($module): void {
        $this->module = $module;
    }

    function setError($error): void {
        $this->error = $error;
    }

    function setErrorText($errorText): void {
        $this->errorText .= $errorText . "\n";
    }

    function setFiles($files): void {
        $this->files = $files;
    }

    function setRowId($rowId): void {
        $this->rowId = $rowId;
    }

    function setDomain($domain): void {
        $this->domain = $domain;
    }

    function setFolder($folder): void {
        if (substr($folder, -1) != "/") {
            $folder .= "/";
        }
        $tmp = str_replace(":", "_", $folder);
        if (file_exists($tmp)) {//Tapaus 1
            $this->folder = $tmp;
        } else {//Objektissa on ollut piste
            $tmp = str_replace(":", "_", $folder);
            //26.9.2022 Halutaan hakemistoon jättää pistenimi
            //$tmp = str_replace(".", "_", $tmp);
        }
        if (!file_exists($tmp)) {
//            die("Ei saada hakemistoa");
            $this->setErrorText("Objektille ei löydy hakemistoa hakemistosta: " . PICTURE_FOLDER);
            $this->setError("-20");
        }
        $this->folder = $tmp;
    }

    function setData($data): void {
        $this->data = $data;
    }

    function setKokoelmatunnus($kokoelmatunnus): void {
        $this->kokoelmatunnus = $kokoelmatunnus;
    }

    /**
     * Mahdollista ajaa kahdenlaista ajoa M+ järjestelmään. Ensin tehdään tiedostojen lisäys vain aineistoille, joiden moduleId löytyy m+ järjestelmästä
     * @param string $type (image) // Tällä hetkellä ainoa vaihtoehto. Tämä tieto otetaan tiedostosta
     * @param string $filename (basename)
     * @param int sequence
     * @return type
     */
    public function getMetadata($file_object) {
        if ($this->cataloged == true) {
            return $this->getDataToMuseum($file_object);
        } else {
            /*
             * 2	Käyttötapaus 2
             * Sovellus vie ja luo kuvalle uuden objektitietueen ja liitetiedostotietueen.
             * Tässä käyttötapauksessa objektia ei vielä ole olemassa kokoelmanhallintajärjestelmässä.
             * Objektille on luotava tietue kokoelmanhallintajärjestelmään viennin yhteydessä.
             * Vaatii määrittelyn, miten ohjelma toimii näissä tapauksissa. Oletettavasti käytetään
             * samaa metodia kuin käyttötapauksessa 1, mutta liitetiedoston nimen perusteella on
             * luotava uusi objektitietue/liitetiedostotietue.
             */
            return null;
        }
    }

    /**
     * Tässä vaiheessa hakemistolle on jo haettu objekti_ID M+ järjestelmästä
     * Tehdään tiedostokohtainen XML tietue. Tietueen mukana seuraavat tiedot:
     * @param OOP $file_object
     * @return array (data, xml)
     */
    private function getDataToMuseum($file_object) {
        $xml = "";
        $return = array();
        $return["Multimedia_OrgID"] = $this->getDomain();
        $return["MulTypeVoc"] = $file_object->getType();
        $return["MulOriginalFileDpl"] = $file_object->getBasename();
        $data = array("getCollectionDataToXML" => 1, "name" => $this->getKokoelmatunnus());
        $data_response = callRest("POST", WEBROOT . "/rest/collections.php", $data, true); //Is collection folder ready...

        if (!isset($data_response->object_id) || $data_response->object_id == "") {
            $this->setError(14);
            $this->setErrorText("Aineistolla ei ole objektiID:tä. Tai aineistoa ei ole merkitty valmiiksi.");
            $this->changeStatus(0, "lahetys");
            return null;
        }

        if (strpos($file_object->getBasename(), "dng") === false) {
            if (isset($data_response->sisainen_tyyppi) && isset($data_response->sisainen_oikeus)) { //Lisenssi
                $xml .= $this->licenseXML($data_response->sisainen_oikeus, $data_response->sisainen_tyyppi);
            }
            if ($data_response->finna == 1) {
                $xml .= $this->finnaXML();
            }
            $xml .= $this->imageFormatXML($file_object->getBasename());
        }
        $return["MulReferencesCre"] = $data_response->object_id; //käytetään rajapinnalla
        if (is_numeric($data_response->tekija_id) && $data_response->tekija_id != "") {
            $xml .= $this->MulPhotographerRef($data_response->tekija_id);
        }
        $xml .= $this->imageTypeXML($file_object->getType());
        $xml = $this->makeXmlHeaders($xml, $data_response->object_id, $file_object->getSequence(), $file_object->getExtension());
        $message = writeLog("XML (getDataToMuseum): $xml", basename($file_object->getFolder()) . "_xml", false);
        return array("data" => $return, "xml" => $xml);
    }

    /**
     * Lähetetään objektin ilmentymän tiedot ja itse tiedosto
     * @param array $file
     * @return int  -1 / file_object_id
     */
    public function sendMultimediaContent($file) {
        $xml = $file->getXml();
        $tried = 0;
        $viesti = null;
        $object_id = $file->getData()["MulReferencesCre"];
        if ($xml != "") {
            while (RE_TRIES > $tried) {
                //Lähetetään tiedoston tiedot ja saadaan vastaukseksi uusi IlmentymäId
                $data = array("sendMPlus" => base64_encode($xml), "moduleId" => $object_id);
                $id = callRest("POST", WEBROOT . "/rest/sendToMplus.php", $data);
                if ($id >= 1) {
                    $tried = 100; //Just bigger than re_tries
                    $tmp_data = array("makeReady" => 1, "file" => $file->getBasename(), "folder" => $file->getFolder(), "column" => "file_object_id", "status" => $id);
                    $paivitys = callRest("POST", WEBROOT . "/rest/files.php", $tmp_data, true);
                    $viesti .= "Tiedoston metatiedot " . $file->getBasename() . " on lähetettty onnistuneesti M+ järjestelmään!\n";
                    if ($paivitys != 1) {
                        $viesti .= ". Tietoa ei saatu tallennettua tietokantaan: $paivitys\n";
                        $viesti = writeLog($viesti, basename($file->getFolder()), true);
                    } else {
                        $viesti = writeLog($viesti, basename($file->getFolder()), false);
                    }

                    $viesti = writeLog("XML STARTS OBJECT ID: $object_id\n $xml \n XML STOPS", basename($file->getFolder()) . "_xml", false);
                    return $id;
                }
                $tried++;
            }
            if (!is_numeric($id)) {
                $viesti .= "Tiedoston metatietoja yritetttiin viedä $tried kertaa MuseumPlus järjestelmään. Vienti epäonnistui.";
                $viesti = writeLog("$id\n", basename($file->getFolder()) . "_mplus", true);
                $viesti = writeLog($viesti, basename($file->getFolder()), true);
                $this->changeStatus(-1);
                return -1;
            }
        }
    }

    public function sendMultimediaFile($file, $file_object_id) {
        $xml = $file->getXml();
        $tried = 0;
        $viesti = null;
        if ($xml != "") {
            while (RE_TRIES > $tried) {
                //Lähetetään tiedoston tiedot ja saadaan vastaukseksi uusi IlmentymäId
                $tmp_data = array("MulOriginalFileDpl" => 1, "file" => $file->getStaticName(), "moduleId" => $file_object_id);
                $ok = callRest("POST", WEBROOT . "/rest/sendToMplus.php", $tmp_data);
                if ($ok == 1) {
                    $tried = 100; //Just bigger than re_tries
                    $tmp_data = array("makeReady" => 1, "file" => $file->getBasename(), "folder" => $file->getFolder(), "column" => "lahetetty", "status" => 2);
                    $paivitys = callRest("POST", WEBROOT . "/rest/files.php", $tmp_data, true);
                    $viesti .= "Tiedosto " . $file->getBasename() . " saatiin lähetttyä M+ järjestelmään. ID: $file_object_id\n";
                    if ($paivitys != 1) {
                        $viesti .= ". Tietoa ei saatu tallennettua tietokantaan: $paivitys\n";
                        $viesti = writeLog($viesti, basename($file->getFolder()), true);
                    } else {
                        $viesti = writeLog($viesti, basename($file->getFolder()), false);
                    }
                    return 1;
                }
                $tried++;
            }
            if ($ok != 1) {
                $viesti = "Tiedostoa " . $file->getBasename() . " yritettiin viedä $tried kertaa MuseumPlus järjestelmään. Vienti epäonnistui.";
                $viesti = writeLog("$ok\n", basename($file->getFolder()) . "_mplus", true);
                $viesti = writeLog($viesti, basename($file->getFolder()), true);
                return -1;
            }
        }
    }

    public function disableOldThumbnails($id) {
        $tried = 0;
        while (RE_TRIES > $tried) {
            $tmp_data = array("moduleId" => $id);
            $ok = callRest("POST", WEBROOT . "/rest/disableThumbnails.php", $tmp_data);
            if ($ok != "") {
                $tried = 100;
            } else {
                $ok = -1;
                $tried++;
            }
        }
        return $ok;
    }

    public function changeStatus($status, $phase = "metatiedot") {
        if ($this->getRowId() != 0) {
            $data1 = array("row_id" => $this->getRowId(), "phase" => $phase, "status" => $status);
            $response = callRest("POST", WEBROOT . "/rest/changeProsessingStatus.php", $data1, true);
            return $response;
        } else {
            return 0;
        }
    }

    public function saveCountOfTheTries() {
        $data1 = array("saveCountOfTheTries" => 1, "row_id" => $this->getRowId());
        $response = callRest("POST", WEBROOT . "/rest/collections.php", $data1, true);
        return $response;
    }

    public function getChangeNameXml($filename, $file_id) {
        //M+ ei laita nimeä oikein. Tehty tälle uusi xml
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<application xmlns=\"http://www.zetcom.com/ria/ws/module\">\n";
        $xml .= "  <modules>\n";
        $xml .= "    <module name=\"Multimedia\">\n";
        $xml .= "      <moduleItem id='$file_id'>\n";
        $xml .= "        <dataField dataType='Varchar' name='MulOriginalFileTxt'>\n";
        $xml .= "           <value>$filename</value>\n";
        $xml .= "        </dataField>\n";
        $xml .= "      </moduleItem>\n";
        $xml .= "    </module>\n";
        $xml .= "  </modules>\n";
        $xml .= "</application>";
        return $xml;
    }

    private function makeXmlHeaders($xml, $module_id, $sequence, $extension) { //Tehdään XML tietue
        global $thumb_ok;
        /* $msg = "<?xml version='1.0' encoding='UTF-8'?>\n"; */
        $msg = "<application xmlns='http://www.zetcom.com/ria/ws/module'>\n";
        $msg .= "   <modules>\n";
        $msg .= "       <module name='" . $this->getModule() . "'>\n";
        if ($this->getModule() == "multimedia") {
            $msg .= "           <moduleItem>\n";
        }
        $msg .= $xml;

        $msg .= "<composite name='MulReferencesCre'>\n";
        $msg .= "   <compositeItem>\n";
        $msg .= "       <moduleReference name='MulObjectRef' targetModule='Object'>\n";
        $msg .= "           <moduleReferenceItem moduleItemId='$module_id'>\n";
//        $msg .= "<dataField name='$filename'>\n";
        if (strtolower($extension) == "tif" || strtolower($extension) == "jpg") {
            if ($sequence == 1 && $thumb_ok == false) {
                $thumb_ok = true;
                $msg .= "<dataField name='ThumbnailBoo'>\n";
                $msg .= "<value>true</value>\n";
                $msg .= "<formattedValue language='en'>yes</formattedValue>\n";
                $msg .= "</dataField>\n";
            }
        }

        $msg .= "           </moduleReferenceItem>\n";
        $msg .= "       </moduleReference>\n";
        $msg .= "   </compositeItem>\n";
        $msg .= "</composite>\n";

        if ($this->getModule() == "multimedia") {
            $msg .= "           </moduleItem>\n";
        }
        $msg .= "       </module>\n";
        $msg .= "   </modules>\n";
        $msg .= "</application>\n";
        return $msg;
    }

    /**
     * Tekijä
     * @param int $value
     * @return part of xml
     */
    private function MulPhotographerRef($value) {
        $xml = "<moduleReference name='MulPhotographerRef' targetModule='Address'>\n";
        $xml .= "   <moduleReferenceItem moduleItemId='$value'>\n";
        $xml .= "   </moduleReferenceItem>\n";
        $xml .= "</moduleReference>\n";
        return $xml;
    }

    /**
     * Lisenssi.
     * @param int $license (lisenssi)
     * @param int $type (lisenssin tyyppi)
     * @return part of xml
     */
    private function licenseXML($license, $type = 321253) {
        if (!is_numeric($license)) {
            return null;
        }
        $xml = "<repeatableGroup name='MulRightsGrp' size='1'>\n";
        $xml .= "   <repeatableGroupItem>\n";
        $xml .= "       <vocabularyReference name='TypeVoc' id='33621' instanceName='MulRightsTypeVgr'>\n";
        $xml .= "           <vocabularyReferenceItem id='$type'>\n";
        $xml .= "           </vocabularyReferenceItem>\n";
        $xml .= "       </vocabularyReference>\n";
        $xml .= "       <vocabularyReference name='RightNBA01Voc' id='60618' instanceName='MulRightsRightNBA01Vgr'>\n";
        $xml .= "           <vocabularyReferenceItem id='$license'>\n";
        $xml .= "           </vocabularyReferenceItem>\n";
        $xml .= "       </vocabularyReference>\n";
        $xml .= "   </repeatableGroupItem>\n";
        $xml .= "</repeatableGroup>\n\n";
        return $xml;
    }

    /** Huuhaata
     * Oikeus. (edustalla oikeustyyppi)
     * @param int $value
     * @return part of xml
     */
    /*
      private function rightsXML($value = 33621) {
      $xml = "<repeatableGroup name='MulRightsGrp'>\n";
      $xml .= "   <repeatableGroupItem>\n";
      $xml .= "       <vocabularyReference name='RightNBA01Voc'>\n";
      $xml .= "           <vocabularyReferenceItem id='60618'/>\n";
      $xml .= "       </vocabularyReference>\n";
      $xml .= "       <vocabularyReference name='TypeVoc'>\n";
      $xml .= "           <vocabularyReferenceItem id='$value'/>\n";
      $xml .= "       </vocabularyReference>\n";
      $xml .= "   </repeatableGroupItem>\n";
      $xml .= "</repeatableGroup>\n";
      return $xml;
      }
     */

    /**
     * Finna täppä
     * @param int $value
     * @return part of xml
     */
    private function finnaXML($value = 321715) {
        $xml = "<repeatableGroup name='MulPublStatusNBA01Grp'>\n";
        $xml .= "   <repeatableGroupItem>\n";
        $xml .= "       <vocabularyReference name='StatusVoc'>\n";
        $xml .= "           <vocabularyReferenceItem id='118575'/>\n";
        $xml .= "       </vocabularyReference>\n";
        $xml .= "       <vocabularyReference name='TypeVoc'>\n";
        $xml .= "           <vocabularyReferenceItem id='$value'/>\n"; //
        $xml .= "       </vocabularyReference>\n";
        $xml .= "   </repeatableGroupItem>\n";
        $xml .= "</repeatableGroup>\n";
        return $xml;
    }

    /**
     * Tiedoston tyyppi. Oletuksena kuva
     * @param string $type (VAIN image tällä hetkellä)
     * @return part of xml
     */
    private function imageTypeXML($type) {
        $value = 105829;
        if (strtolower($type) == "image") {
            $value = 105829;
        }
        $xml = "<vocabularyReference name='MulTypeVoc' id='30341' instanceName='MulTypeVgr' >\n";
        $xml .= "    <vocabularyReferenceItem id='$value'>\n";
        $xml .= "    </vocabularyReferenceItem>\n";
        $xml .= "</vocabularyReference>\n";

        return $xml;
    }

    /**
     * Filename or filetype
     * @param string filename OR tif/jpg
     * @return part of xml
     */
    private function imageFormatXML($filename) {
        $tmp = strtolower($filename);
        if (strpos($tmp, "tif") === true) {
            $value = 198034;
        } elseif (strpos($tmp, "jpg") === true) {
            $value = 59200;
        } else {
            return null;
        }
        $xml = "<vocabularyReference name='MulFormatVoc' id='30331' instanceName='MulFormatVgr' >\n";
        $xml .= "    <vocabularyReferenceItem id='$value'>\n";
        $xml .= "    </vocabularyReferenceItem>\n";
        $xml .= "</vocabularyReference>\n";
        return $xml;
    }

}

/**
 * @read_exif_data == Put notices to trash because different file types...
 */
class ProcessingFile {

    private $folder;
    private $filename;
    private $basename;
    private $staticName;
    private $extension;
    private $type;
    private $notFound = "Unavailable";
    private $data;
    private $error;
    private $errorText;
    private $xml;
    private $sequence;

    public function __construct($folder = "", $filename = "") {
        $this->error = 0;
        if (substr($folder, -1) != "/") { {
                $folder .= "/";
            }
        }
        $this->folder = $folder;
        $this->staticName = $folder . $filename;
        $path_parts = pathinfo($this->staticName);
        if (isset($path_parts["extension"])) {
            $this->extension = $path_parts["extension"];
        }

        $this->type = getExtensionType($this->extension); //Esine tai kuva (object or image)
        $this->filename = $path_parts["filename"];
        $this->basename = $filename;
        if ($this->type == "") {
            $this->error = 1;
            $this->errorText = "Tiedostopäätettä ei löydy tiedostotyypit (TYPES) listalta. ";
        }
        $this->data["camera"] = $this->getCameraUsed();
        $this->data["metadata"] = $this->getImageMetadata();
    }

    function getFolder() {
        return $this->folder;
    }

    /**
     * ex. HK8206_9
     * @return string
     */
    function getFilename() {
        return $this->filename;
    }

    /**
     * ex. HK8206_9.tif
     * @return string
     */
    function getBasename() {
        return $this->basename;
    }

    function getStaticName() {
        return $this->staticName;
    }

    function getExtension() {
        return $this->extension;
    }

    function getType() {
        return $this->type;
    }

    function getError() {
        return $this->error;
    }

    function getErrorText() {
        return $this->errorText;
    }

    function getData() {
        return $this->data;
    }

    function getXml() {
        return $this->xml;
    }

    function getSequence() {
        $tmp = explode("__", $this->getFilename());
        if (!isset($tmp[1])) {
            $this->sequence = 1;
        } else {
            $this->sequence = $tmp[1];
        }

        return $this->sequence;
    }

    function setXml($xml): void {
        $this->xml .= $xml;
    }

    function setBasename($basename): void {
        $this->basename = $basename;
    }

    function setData($data): void {
        $this->data = $data;
    }

    function setError($error): void {
        $this->error = $error;
    }

    function setErrorText($errorText): void {
        $this->errorText = $errorText;
    }

    function setType($type): void {
        $this->type = $type;
    }

    function setFolder($folder): void {
        $this->folder = $folder;
    }

    function setFilename($filename): void {
        $this->filename = $filename;
    }

    function setStaticName($staticName): void {
        $this->staticName = $staticName;
    }

    function setExtension($extension): void {
        $this->extension = $extension;
    }

    private function getCameraUsed() {
// Check if the variable is set and if the file itself exists before continuing
// There are 2 arrays which contains the information we are after, so it"s easier to state them both
        $exif_ifd0 = @read_exif_data($this->staticName, "IFD0", 0);
        $exif_exif = @read_exif_data($this->staticName, "EXIF", 0);
// Make
        if (@array_key_exists("Make", $exif_ifd0)) {
            $camMake = $exif_ifd0["Make"];
        } else {
            $camMake = $this->notFound;
        }
// Model
        if (@array_key_exists("Model", $exif_ifd0)) {
            $camModel = $exif_ifd0["Model"];
        } else {
            $camModel = $this->notFound;
        }
// Exposure
        if (@array_key_exists("ExposureTime", $exif_ifd0)) {
            $camExposure = $exif_ifd0["ExposureTime"];
        } else {
            $camExposure = $this->notFound;
        }
// Aperture
        if (@array_key_exists("ApertureFNumber", $exif_ifd0["COMPUTED"])) {
            $camAperture = $exif_ifd0["COMPUTED"]["ApertureFNumber"];
        } else {
            $camAperture = $this->notFound;
        }
// Date
        if (@array_key_exists("DateTime", $exif_ifd0)) {
            $camDate = $exif_ifd0["DateTime"];
        } else {
            $camDate = $this->notFound;
        }
// ISO
        if (@array_key_exists("ISOSpeedRatings", $exif_exif)) {
            $camIso = $exif_exif["ISOSpeedRatings"];
        } else {
            $camIso = $this->notFound;
        }
        if (@array_key_exists("Orientation", $exif_ifd0)) {
            $orientation = $exif_ifd0["Orientation"];
        } else {
            $orientation = $this->notFound;
        }
        $return["make"] = $camMake;
        $return["model"] = $camModel;
        $return["exposure"] = $camExposure;
        $return["aperture"] = $camAperture;
        $return["date"] = $camDate;
        $return["iso"] = $camIso;
        $return["orientation"] = $orientation;
        $return["orientation_name"] = $this->getOrientationName($orientation);
        return $return;
    }

    private function getImageMetadata() {
        $exif_ifd0 = @read_exif_data($this->staticName, "IFD0", 0);
        if (@array_key_exists("Artist", $exif_ifd0)) {
            $artist = $exif_ifd0["Artist"];
        } else {
            $artist = $this->notFound;
        }
        $return["artist"] = $artist;
        return $return;
    }

    /**
     *
     * @param int $ort 1-8
     * @return string orientationType
     */
    private function getOrientationName($ort) {
        $tmp = $this->notFound;
        switch ($ort) {
            case 1: // nothing
                $tmp = "normal";
                break;

            case 2: // horizontal flip
                $tmp = "horizontal flip";
                break;

            case 3: // 180 rotate left
                $tmp = "180 rotate left";
                break;

            case 4: // vertical flip
                $tmp = "vertical flip";
                break;

            case 5: // vertical flip + 90 rotate right
                $tmp = "vertical flip + 90 rotate right";
                break;

            case 6: // 90 rotate right
                $tmp = "90 rotate right";
                break;

            case 7: // horizontal flip + 90 rotate right
                $tmp = "horizontal flip + 90 rotate right";
                break;

            case 8:    // 90 rotate left
                $tmp = "90 rotate left";
                break;
        }
        return $tmp;
    }

}
