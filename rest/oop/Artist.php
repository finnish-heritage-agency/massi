<?php

/**
 * Description of Artist
 *
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class Artist {

    private $db;
    private $artistId;
    private $name; //name
    private $internalId; //To museum+
    private $active;

    public function __construct($db, $artistId = 0, $name = "", $internalId = 0, $active = 0) {
        $this->db = $db;
        $this->artistId = $artistId;
        $this->name = $name;
        $this->internalId = $internalId;
        $this->active = $active;
    }

    public function getAllArtists($active) {
        $more = null;
        if ($active == 1) {
            $more = "WHERE aktiivinen = 1";
        }

        try {
            $sql = "SELECT * FROM tekijat $more ORDER BY aktiivinen DESC";
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
                //$array[] = array($artist = new Artist($row["tekija_id"], $row["teksti"], $row["sisainen_id"], $row["aktiivinen"])); //NOUH
                $array[] = array(//Licenses function has the sames
                    "id" => $row["tekija_id"],
                    "teksti" => $row["nimi"],
                    "sisainen_nimi" => $row["sisainen_id"],
                    "aktiivinen" => $row["aktiivinen"]
                );
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $array;
    }

    public function changeActiveStatus($artist_id, $active = 0, $again = false) {
        $return = 1;
        try {
            $sql = "UPDATE tekijat SET aktiivinen =:active WHERE tekija_id =:artist_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":artist_id", $artist_id);
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
                $return = $this->changeActiveStatus($artist_id, $active, true);
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $return;
    }

    public function deleteArtist($artist_id) {
        try {
            $sql = "DELETE FROM tekijat WHERE tekija_id =:artist_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":artist_id", $artist_id);
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
    public function addAnArtist() {
        try {
            $sql = "INSERT INTO tekijat (nimi, sisainen_id) VALUES (:name, :internal_id)";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":name", $this->name);
            $stmt->bindParam(":internal_id", $this->internalId);
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
