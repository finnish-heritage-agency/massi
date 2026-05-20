<?php

class Job {

    private $jobId;
    private $rowId; //Listan rivi_id
    private $collectionName;
    private $startTime;
    private $jobPhases = JOB_PHASES;
    private $jobPhaseStatuses;
    private $logFile;

    public function __construct($job_id = 0, $row_id = 0, $collection_name = "") {
        $this->jobId = $job_id;
        $this->rowId = $row_id;
        $this->collectionName = $collection_name;
        //26.9.2022 Halutaan hakemistoon jättää pistenimi
        //$tmp = str_replace(".", "_", $this->collectionName);
        $tmp = $this->collectionName;
        $this->logFile = LOGS . str_replace(":", "_", $tmp) . "-log";

//        $this->nextPhase = $this->getNextPhaseFromArray();
    }

    function getJobPhaseStatuses() {
        return $this->jobPhaseStatuses;
    }

    function getStartTime() {
        if ($this->startTime != "") {
            return date("d.m.Y H:i:s", strtotime($this->startTime));
        } else {
            return null;
        }
    }

    function getLogFile() {
        return $this->logFile;
    }

    function setJobPhaseStatuses($jobPhaseStatuses): void {
        $this->jobPhaseStatuses = $jobPhaseStatuses;
    }

    function setStartTime($startTime): void {
        $this->startTime = $startTime;
    }

    private function getNextPhaseFromArray() {
        $key = (int) array_search($this->phase, $this->jobPhases);
        $key++;
        if (array_key_exists($key, $this->jobPhases)) {
            return $this->jobPhases[$key];
        } else {
            return $this->phase;
        }
    }

}
