<?php

/**
 * Description of File
 *
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class File {

    private $db;
    private $rowId;
    private $folder;
    private $filename;
    private $staticName;

    public function __construct($db, $folder = "", $filename = "", $row_id = 0) {
        $this->db = $db;
        $this->setFolder($folder);
        $this->filename = $filename;
        $this->rowId = $row_id;
        $this->staticName = $folder . $filename;
    }

    function getFolder() {

        return $this->folder;
    }

    function setFolder($folder): void {
        if (substr($folder, -1) != "/") {
            $folder .= "/";
        }
        $this->folder = $folder;
    }

    function getRowId() {
        return $this->rowId;
    }

    function setRowId($rowId): void {
        $this->rowId = $rowId;
    }

    public function saveFile() {
        try {
            $sql = "INSERT INTO tiedostot (listan_rivi_id, hakemisto, tiedosto) values (:row_id, :folder, :file)";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":row_id", $this->rowId);
            $stmt->bindValue(":folder", $this->getFolder());
            $stmt->bindParam(":file", $this->filename);

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

    public function removeFiles() {
        try {
            $sql = "DELETE FROM tiedostot WHERE listan_rivi_id = :row_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":row_id", $this->rowId);
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

    public function changeFileColumnStatus($column, $status) {
        /*
          if ($column != "xml_luotu" && $column != "valmis") { //Tarkistetaan että ei mene sql kyselyyn mitään turhaa
          return -5;
          }
         *
         */
        try {
            $sql = "UPDATE tiedostot SET `$column` = :status WHERE hakemisto = :folder AND tiedosto = :file";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":status", $status);
            $stmt->bindValue(":folder", $this->getFolder());
            $stmt->bindParam(":file", $this->filename);

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

}
