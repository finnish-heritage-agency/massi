<?php

/**
 * Description of License
 * 7.9. License changed to Legal... Thats why there are all parameters like license...
 * License = Lisenssi
 * Legal = oikeus...
 *
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class Legal {

    private $db;
    private $licenceId;
    private $title;
    private $internalName; //To museum+
    private $active;

    public function __construct($db, $licenceId = 0, $title = "", $internalName = "", $active = 0) {
        $this->db = $db;
        $this->licenceId = $licenceId;
        $this->title = $title;
        $this->internalName = $internalName;
        $this->active = $active;
    }

    public function getAllLegals($active) {
        $more = null;
        if ($active == 1) {
            $more = "WHERE aktiivinen = 1";
        }

        try {
            $sql = "SELECT * FROM oikeudet $more ORDER BY aktiivinen DESC";
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
                $array[] = array(
                    "id" => $row["lisenssi_id"],
                    "teksti" => $row["teksti"],
                    "sisainen_nimi" => $row["sisainen_nimi"],
                    "aktiivinen" => $row["aktiivinen"]
                );
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $array;
    }

    public function changeActiveStatus($license_id, $active = 0, $again = false) {
        $return = 1;
        try {
            $sql = "UPDATE oikeudet SET aktiivinen =:active WHERE lisenssi_id =:license_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":license_id", $license_id);
            $stmt->bindParam(":active", $active);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            if ($stmt->rowCount() == 0 && $again == false) { //If we dont know artist´s currently status
                if ($active == 0) {
                    $active = 1;
                } else {
                    $active = 0;
                }
                $return = $this->changeActiveStatus($license_id, $active, true);
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    public function deleteLegal($legal_id) {
        try {
            $sql = "DELETE FROM oikeudet WHERE lisenssi_id =:license_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":license_id", $legal_id);
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

    /**
     *
     * @return int. IF int is above 0, all is OK
     */
    public function addLegal() {
        try {
            $sql = "INSERT INTO oikeudet (teksti, sisainen_nimi) VALUES (:name, :internal_name)";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":name", $this->title);
            $stmt->bindParam(":internal_name", $this->internalName);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $id = $this->db->lastInsertId();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $id;
    }

}
