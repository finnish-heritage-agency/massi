<?php

/**
 * Description of JobStatus
 * Lisätään ja muokataan jonossa olevien rivien statuksia
 * @author Jouni Repo <jouni@softrepo.fi>
 */
class JobStatus {

    private $db;
    private $rowId;
    private $phase;
    private $status;

    public function __construct($db, $row_id = 0, $phase = "", $status = 0) {
        $this->db = $db;
        $this->rowId = $row_id;
        $this->phase = $phase;
        $this->status = $status;
    }

    function getRowId() {
        return $this->rowId;
    }

    function getPhase() {
        return $this->phase;
    }

    function getStatus() {
        return $this->status;
    }

    function setRowId($rowId): void {
        $this->rowId = $rowId;
    }

    function setPhase($phase): void {
        $this->phase = strtolower($phase);
    }

    function setStatus($status): void {
        $this->status = $status;
    }

    public function addANewJob() {
        try {
            $sql = "INSERT INTO tyot (listan_rivi_id, aloitettu) VALUES (:rowId,  NOW())";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rowId", $this->rowId);

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

    public function addAllRowsByListaId($lista_id) {
        $rows = $this->getRowsByListaId($lista_id);
        $count = 0;
        foreach ($rows as $row) {
            $this->setRowId($row["rivi_id"]);
            $ok = $this->addANewJob();
            if ($ok < 0) {
                return $ok;
            } else {
                $count++;
            }
        }
        return $count;
    }

    public function getId($name) {
        $sql = "SELECT lista_id FROM listan_rivit WHERE kokoelmatunnus = :tunnus AND valmis = 1";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":tunnus", $name);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $tmp = $stmt->fetchColumn();
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        $this->setRowId($tmp);
        return $tmp;
    }

    /**
     * Get jobs by status and phase
     * Rows which has not ready
     */
    public function getCurrentJobs() {
        if ($this->getPhase() == "tarkistus") {
            $lisays = " AND T.metatiedot >= 0";
        } elseif ($this->getPhase() == "lahetys") {
            $lisays = " AND T.metatiedot = 2 AND T.lahetys = 2";
        } else {
            $lisays = " AND T.metatiedot >= 0 AND T.lahetys >= 0 ";
        }
        $sql = "SELECT T.*, LR.kokoelmatunnus, LR.objektin_id
                FROM tyot T
                LEFT JOIN listan_rivit LR
                ON LR.rivi_id = T.listan_rivi_id WHERE T." . $this->getPhase() . " = :status AND T.rivi_valmis = 0 $lisays LIMIT 10";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $status = $this->getStatus();
            $stmt->bindParam(":status", $status);
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

    public function checkCollectionId($collection_id) {
        $sql = "SELECT lista_id FROM listan_rivit WHERE kokoelmatunnus = :kokoelmatunnus AND valmis = 2";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":kokoelmatunnus", $collection_id);
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

    public function getAllJobs($all = false) {
        if ($all == false) {
            $more_sql = "AND listan_rivit.valmis <> 2";
            $more_column = "";
        } else {
            $more_sql = "AND listan_rivit.lista_id = listat.lista_id";
            $more_column = ", listat";
        }
        $sql = "SELECT * FROM listan_rivit, tyot $more_column WHERE listan_rivit.rivi_id = tyot.listan_rivi_id $more_sql";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }

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

    public function getOneJobForLogs($id, $column = "lista_id") {

        //$sql = "SELECT * FROM listan_rivit, tyot WHERE listan_rivit.rivi_id = tyot.listan_rivi_id AND listan_rivit.lista_id = :lista_id AND listan_rivit.valmis <> 2 GROUP BY rivi_id";
        $sql = "SELECT * FROM listan_rivit, tyot WHERE listan_rivit.rivi_id = tyot.listan_rivi_id AND listan_rivit.$column = :id GROUP BY rivi_id";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":id", $id);
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

    public function getOneJobByErrors($lista_id) {
        $sql = "SELECT * FROM listan_rivit, tyot WHERE listan_rivit.rivi_id = tyot.listan_rivi_id AND listan_rivit.lista_id = :lista_id AND
            (tyot.metatiedot =-1 OR tyot.tarkistus = -1 OR tyot.lahetys = -1 OR tyot.nayttokuvat = -1)
            ORDER BY tyot.aloitettu DESC";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":lista_id", $lista_id);
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

    /**
     *
     * @param int $job_id (lista_id)
     * @return array
     */
    public function getOneJob($lista_id) {
        $sql = "SELECT * FROM listan_rivit, tyot WHERE listan_rivit.rivi_id = tyot.listan_rivi_id AND listan_rivit.lista_id = :lista_id ORDER BY tyot.aloitettu DESC";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":lista_id", $lista_id);
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

    public function changeStatus($error_code = null) {
        if ($this->getRowId() < 0) {
            return null;
        }
        $sql = "UPDATE tyot SET " . $this->getPhase() . " = :status WHERE listan_rivi_id = :rivi_id ";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $this->rowId);
            $stmt->bindParam(":status", $this->status);
            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            if ($stmt->rowCount() == 0 && $this->jobFound() == 0) {
                //Double check. if collection row is ready but some how it is not in the työt table... (Not possible, but just in case)
                $this->addANewJob();
                $this->changeStatus();
                $this->storePhase();
            } else {
                $this->storePhase();
            }
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        if ($this->status < 0) {
            $this->markFailed($error_code);
        }
        return 1;
    }

    public function markRetry($status) {
        $sql = "UPDATE listan_rivit SET valmis=:valmis WHERE rivi_id = :rivi_id ";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $this->rowId);
            $stmt->bindParam(":valmis", $status); //1, tarkoittaa, että ajetaan uudestaan

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

    public function markFailed($error_code) {
        $more_sql = null;
        if ($error_code != null) {
            $more_sql = ", error=:error";
        }
        $sql = "UPDATE listan_rivit SET valmis = :status $more_sql WHERE rivi_id = :rivi_id ";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $this->rowId);
            $stmt->bindParam(":status", $this->status);
            if ($error_code != null) {
                $stmt->bindParam(":error", $error);
            }

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

    public function removeObjectId() {
        $sql = "UPDATE tiedostot SET file_object_id = 0, lahetetty = 0 WHERE listan_rivi_id = :rivi_id ";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $this->rowId);

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

    private function getRowsByListaId($lista_id) {
        try {
            $sql = "SELECT rivi_id FROM listan_rivit WHERE lista_id = :lista_id";
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":lista_id", $lista_id);

            if (!$stmt->execute()) {
                writeToLog("Cannot execute prepared statement for: $sql");
                return -2;
            }
            $id = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $error) {
            writeToLog($error->getMessage());
            return -1;
        }
        return $id;
    }

    private function storePhase() {
        $sql = "INSERT INTO tyo_statukset (listan_rivi_id, vaihe, vaihe_nro, paivays) VALUES "
                . "(:rivi_id, :vaihe, :vaihe_nro, NOW())";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            if ($this->getPhase() == "valmistunut") {
                $this->setStatus(2); //if marking job done, in the status field we have the current time...
            }
            $stmt->bindValue(":rivi_id", $this->rowId);
            $stmt->bindValue(":vaihe", $this->getPhase());
            $stmt->bindValue(":vaihe_nro", $this->status);

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

    private function jobFound() {
        $sql = "SELECT count(*) FROM tyot WHERE listan_rivi_id = :rivi_id";
        try {
            if (!$stmt = $this->db->prepare($sql)) {
                writeToLog("Cannot prepare stament: $sql");
                return -3;
            }
            $stmt->bindParam(":rivi_id", $this->rowId);

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
}
