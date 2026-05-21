<?php

/**
 * Description of Collection
 *
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class Collection {

    private $db;
    private $id;
    private $collectionTitle;
    private $finna;
    private $licenseId;
    private $legalTypeId;
    private $artistId;
    private $batchSaver;

    public function __construct($db) {
        $this->db = $db;
        $this->licenseId = 0;
        $this->legalTypeId = 0;
        $this->artistId = 0;
        $this->finna = 0;
    }

    function getCollectionTitle() {
        return $this->collectionTitle;
    }

    function getFinna() {
        return $this->finna;
    }

    function getLicenseId() {
        return $this->licenseId;
    }

    function getArtistId() {
        return $this->artistId;
    }

    function getId() {
        return $this->id;
    }

    function getLegalTypeId() {
        return $this->legalTypeId;
    }

    public function getBatchSaver() {
        return $this->batchSaver;
    }

    function setLegalTypeId($legalTypeId): void {
        $this->legalTypeId = $legalTypeId;
    }

    function setCollectionTitle($collectionTitle): void {
        $this->collectionTitle = $collectionTitle;
    }

    function setId($id): void {
        $this->id = $id;
    }

    function setFinna($finna): void {
        $this->finna = $finna;
    }

    function setLicenseId($licenseId): void {
        if (is_numeric($licenseId)) {
            $this->licenseId = $licenseId;
        } else {
            $this->licenseId = 0;
        }
    }

    function setArtistId($artistId): void {
        if (is_numeric($artistId)) {
            $this->artistId = $artistId;
        } else {
            $this->artistId = 0;
        }
    }

    public function setBatchSaver($batchSaver): void {
        $this->batchSaver = $batchSaver;
    }

    public function addRowsToCollection($param) {
        $param = html_entity_decode($param, ENT_COMPAT | ENT_HTML401, 'UTF-8');

        try {
            $sql = "INSERT INTO listan_rivit (lista_id, kokoelmatunnus) values (:id, :kokoelmatunnus)";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }

            $stmt->bindParam(":id", $this->id);
            $stmt->bindParam(":kokoelmatunnus", $param);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    public function addCollection() {
        $param = html_entity_decode($this->collectionTitle, ENT_COMPAT | ENT_HTML401, 'UTF-8');
        try {
            $sql = "INSERT INTO listat (otsikko, lisenssi_id, tyyppi_id, tekija_id, finna, eran_tallentaja, paivays) values (:title, :license_id, :type_id, :artist_id, :finna, :batch_saver, NOW())";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
//Muutettu get --> parametreihin 1.10.2020
            $stmt->bindParam(":title", $param);
            $stmt->bindParam(":license_id", $this->licenseId);
            $stmt->bindParam(":type_id", $this->legalTypeId);
            $stmt->bindParam(":artist_id", $this->artistId);
            $stmt->bindParam(":finna", $this->finna);
            $stmt->bindParam(":batch_saver", $this->batchSaver);

            if (!$stmt->execute()) {
                $data = "collection: " . $this->collectionTitle . ". lisenssi: " . $this->licenseId . " legal: " . $this->legalTypeId . " " . $this->artistId . " finna: " . $this->finna;
                writeToLog("Cannot execute prepared statement for: $sql $data ");
                return -2;
            }
            $id = $this->db->lastInsertId();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        $this->setId($id);
        return $id;
    }

    public function removeCollection() {
        $collection_id = $this->getId();
        $rows = $this->CollectionRows($collection_id);
        if (count($rows) > 0) {
            $message .= "User removing collection row. ";
            foreach ($rows as $row) {
                $listan_rivi_id = $row["rivi_id"];
                if (!is_numeric($listan_rivi_id)) {
                    continue;
                }
                $message .= "rows: " . $this->removeCollectionRowsDb($listan_rivi_id) . ". ";
                $message .= "statuses: " . $this->removeJobsDb($listan_rivi_id) . ". ";
                $message .= "status rows: " . $this->removeJobStatusesDb($listan_rivi_id) . ". ";
            }
            $message .= "Finally the row: " . $this->removeCollectionDb() . ".\n";
        }
        return $message;
    }

    private function removeCollectionDb() {
        try {
            $sql = "DELETE FROM listat WHERE lista_id = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":id", $this->getId());

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    private function removeCollectionRowsDb($id) { //listan_rivit
        try {
            $sql = "DELETE FROM listan_rivit WHERE lista_id = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":id", $id);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    private function removeJobsDb($id) { //tyot
        try {
            $sql = "DELETE FROM tyot WHERE listan_rivi_id = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":id", $id);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    private function removeJobStatusesDb($id) { //tyo_statukset
        try {
            $sql = "DELETE FROM tyo_statukset WHERE listan_rivi_id = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":id", $id);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    public function allCollections($limit) {
        try {
//            $sql = "SELECT listat.*, COUNT(1) as maara FROM listat,listan_rivit WHERE listat.lista_id = listan_rivit.lista_id GROUP BY lista_id ORDER BY paivays DESC LIMIT $limit";

            $sql = "SELECT L.*, COUNT(1) as maara, LR.*
                FROM listat L
                LEFT JOIN listan_rivit LR
                ON L.lista_id = LR.lista_id
                GROUP BY L.lista_id ORDER BY L.paivays DESC LIMIT $limit";

            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchAll();

            foreach ($tmp as $row) {
                $tmp2 = isReady($row["lista_id"]);
                $array[] = array(
                    "lista_id" => $row["lista_id"],
                    "otsikko" => $row["otsikko"],
                    "paivays" => $row["paivays"],
                    "finna" => $row["finna"],
                    "maara" => $row["maara"],
                    "eran_tallentaja" => $row["eran_tallentaja"],
                    "valmiina" => $tmp2->valmiina,
                    "kesken" => $tmp2->kesken,
                    //"valmiina" => getCompletedRows($row["lista_id"]),
                    //$sql = "SELECT rivi_valmis FROM tyot, listan_rivit WHERE listan_rivit.lista_id = :lista_id AND listan_rivit.rivi_id = tyot.listan_rivi_id";
                    "epaonnistuneet" => $tmp2->epaonnistuneet,
                    "rivi_valmis" => $tmp2->rivi_valmis
                        //"rivi_valmis" => $this->isReady($row["lista_id"])
                );
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $array;
    }

    public function collectionsByObjectName($name) {
        try {
//            $sql = "SELECT listat.*, COUNT(1) as maara FROM listat,listan_rivit WHERE listat.lista_id = listan_rivit.lista_id GROUP BY lista_id ORDER BY paivays DESC LIMIT $limit";

            $sql = "SELECT L.*, COUNT(1) as maara, LR.*
                FROM listat L
                LEFT JOIN listan_rivit LR
                ON L.lista_id = LR.lista_id
                WHERE LR.kokoelmatunnus LIKE ?
                GROUP BY L.lista_id ORDER BY L.paivays";

            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindValue(1, "%$name%", PDO::PARAM_STR);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchAll();

            foreach ($tmp as $row) {
                $tmp2 = isReady($row["lista_id"]);
                $array[] = array(
                    "lista_id" => $row["lista_id"],
                    "otsikko" => $row["otsikko"],
                    "paivays" => $row["paivays"],
                    "finna" => $row["finna"],
                    "maara" => $row["maara"],
                    "eran_tallentaja" => $row["eran_tallentaja"],
                    "valmiina" => $tmp2->valmiina,
                    "kesken" => $tmp2->kesken,
                    //"valmiina" => getCompletedRows($row["lista_id"]),
                    //$sql = "SELECT rivi_valmis FROM tyot, listan_rivit WHERE listan_rivit.lista_id = :lista_id AND listan_rivit.rivi_id = tyot.listan_rivi_id";
                    "epaonnistuneet" => $tmp2->epaonnistuneet,
                    "rivi_valmis" => $tmp2->rivi_valmis
                        //"rivi_valmis" => $this->isReady($row["lista_id"])
                );
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $array;
    }

    /**
     * Response needs to be an array, because of how we use it in collection.php
     * @param int $collection lista_id
     * @return array
     */
    public function oneCollection($collection) {
//        $sql = "SELECT * FROM listat WHERE lista_id = :lista";

        $sql = "SELECT L.*, T.sisainen_id as tekija_id, T.nimi as tekija, OT.sisainen_nimi as sisainen_tyyppi, OT.teksti as tyyppi, LI.sisainen_nimi as sisainen_oikeus, LI.teksti as oikeus
                FROM listat L
                LEFT JOIN oikeudet LI
                ON LI.lisenssi_id = L.lisenssi_id
                LEFT JOIN tekijat T
                ON T.tekija_id = L.tekija_id
                LEFT JOIN oikeustyypit OT
                ON OT.tyyppi_id = L.tyyppi_id
                WHERE L.lista_id = :lista";

        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }

            $stmt->bindParam(":lista", $collection);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $tmp;
    }

    public function CollectionRows($collection_id) {
        //$sql = "SELECT * FROM listan_rivit WHERE lista_id = (SELECT lista_id FROM listan_rivit WHERE kokoelmatunnus = :lista LIMIT 1)";
        try {
            $sql = "SELECT * FROM listan_rivit WHERE lista_id = :lista_id ORDER BY rivi_id ASC";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":lista_id", $collection_id);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $tmp;
    }

    public function checkCollection($collection) {
        try {
//            $sql = "SELECT valmis FROM listan_rivit WHERE kokoelmatunnus = :lista LIMIT 1";
            $sql = "SELECT LR.valmis, LR.objektin_id, LR.rivi_id, T.aloitettu, T.tarkistus
                FROM listan_rivit LR
                LEFT JOIN tyot T
                ON LR.rivi_id = T.listan_rivi_id
                WHERE LR.kokoelmatunnus = :lista LIMIT 1";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return $sql;
            }
            $stmt->bindParam(":lista", $collection);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchObject();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $tmp;
    }

    public function changeCollectionRowStatus($rivi_id, $progress) {
        try {
            $sql = "UPDATE listan_rivit SET valmis=:progress WHERE rivi_id = :rivi_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $rivi_id);
            $stmt->bindParam(":progress", $progress);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    private function getListaId($kokoelmatunnus) {
        try {
            $sql = "SELECT MAX(lista_id) FROM listan_rivit WHERE kokoelmatunnus = :tunnus AND valmis = 1";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $kokoelmatunnus);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchColumn();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $tmp;
    }

    private function getObjectId($kokoelmatunnus) {
        try {
            $sql = "SELECT objektin_id FROM listan_rivit WHERE kokoelmatunnus = :tunnus AND valmis = 1";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $kokoelmatunnus);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchColumn();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $tmp;
    }

    /**
     * Try to find newest row by kokoelmatunnus
     * function Onecollection response is array, so thats why there is that parameter changer...
     * @param string $kokoelmatunnus (HK0000:0)
     * @return object
     */
    public function getCollectionDataToXML($kokoelmatunnus) {
        $lista_id = $this->getListaId($kokoelmatunnus);
        $id = $this->getObjectId($kokoelmatunnus); //Halutaan uusimmasta...
        $collection = $this->oneCollection($lista_id);
        $collection[0]["object_id"] = $id;
        $collection = $collection[0];
        return $collection;
    }

    public function saveObjectId($object_name, $object_id) {
        try {
            $sql = "UPDATE listan_rivit SET objektin_id=:object_id WHERE kokoelmatunnus = :tunnus";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $object_name);
            $stmt->bindParam(":object_id", $object_id);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    public function editCollectionValues() {
        try {
            $sql = "UPDATE listat SET lisenssi_id=:l_id, tyyppi_id=:tyyppi_id, tekija_id = :tekija_id, finna=:finna  WHERE lista_id = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tekija_id", $this->artistId);
            $stmt->bindParam(":tyyppi_id", $this->legalTypeId);
            $stmt->bindParam(":l_id", $this->licenseId);
            $stmt->bindParam(":finna", $this->finna);
            $stmt->bindParam(":id", $this->id);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    public function saveFileCount($object_name, $file_count) {
        try {
            $sql = "UPDATE listan_rivit SET tiedostojen_lkm=:lkm WHERE kokoelmatunnus = :tunnus";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $object_name);
            $stmt->bindParam(":lkm", $file_count);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return 1;
    }

    public function markBatchToReady($row_id) {
        try {
            $sql = "UPDATE listan_rivit SET valmis=1 WHERE lista_id = :lista_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":lista_id", $row_id);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->rowCount();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    public function getfiledata($filename) {
        try {
            $sql = "SELECT * FROM tiedostot WHERE tiedosto = :tiedosto";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tiedosto", $filename);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->fetchObject();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    /**
     * Get all completed collections where date > 14 days from this date
     * @return array
     */
    public function readyToRemove() {
        try {
            $sql = "SELECT tiedostot.hakemisto, listan_rivit.kokoelmatunnus FROM tiedostot, listan_rivit, tyot WHERE listan_rivit.rivi_id = tyot.listan_rivi_id AND tyot.valmistunut IS NOT NULL AND listan_rivit.valmis = 2 "
                    . "AND tyot.listan_rivi_id = tiedostot.listan_rivi_id AND tyot.valmistunut > DATE_ADD(now(), INTERVAL - 30 DAY) AND tyot.valmistunut < DATE_ADD(now(), INTERVAL - 14 DAY) GROUP BY tiedostot.hakemisto";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    /**
     *
     * @param string $folder (/kuvat/KK4372_3518/)
     * @return type
     */
    public function canRemove($folder) {

        try {
            $sql = "SELECT tiedosto, valmis, lahetetty, valmistunut FROM listan_rivit LR, tiedostot T, tyot WHERE T.hakemisto = :hakemisto AND T.listan_rivi_id = LR.rivi_id
                    AND T.listan_rivi_id = LR.rivi_id AND LR.rivi_id = tyot.listan_rivi_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":hakemisto", $folder);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    /**
     *
     * @param type $by_title
     * @return type
     */
    public function saveCountOfTheTries($by_title = false) {
        if ($by_title == true) {
            $column = "kokoelmatunnus";
        } else {
            $column = "rivi_id";
        }
        try {
            $sql = "UPDATE listan_rivit SET yrityksia= yrityksia+1 WHERE $column = :id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if ($by_title == true) {
                $stmt->bindParam(":id", $this->collectionTitle);
            } else {
                $stmt->bindParam(":id", $this->id);
            }

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->rowCount();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    public function resetAttempts() {
        try {
            $sql = "UPDATE listan_rivit SET yrityksia = 0";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->rowCount();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    public function checkAllOk() {
        $return = "";
        try {
            $sql = "SELECT kokoelmatunnus FROM listan_rivit WHERE yrityksia > " . MAX_ATTEMPTS . " LIMIT 1";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->fetchColumn();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        if ($return != "") {
            return $return;
        } else {
            return 1;
        }
    }

    public function checkNewRows($array) {
        foreach ($array as $row) {
            $tmp[] = array("collection" => $row, "digitized" => $this->checkRow($row));
        }
        return $tmp;
    }

    private function checkRow($collectionId) {
        try {
            $sql = "SELECT rivi_id FROM listan_rivit WHERE kokoelmatunnus = :tunnus";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $collectionId);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $return = $stmt->rowCount();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }
}
