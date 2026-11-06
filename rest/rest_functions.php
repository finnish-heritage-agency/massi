<?php

function writeToLog($str) {
    $date = date("d.m.Y", time()); //Filename prefix
    $time = date("d.m.Y H:i:s", time());
    $lokifile = "restService-$date.log";
    $str = print_r($str, true);
    /*
      file_put_contents("./logs/restService-$date.log", "$data", FILE_APPEND);
      file_put_contents("./logs/restService-$date.log", " --> $str\n", FILE_APPEND);
     *
     */


    if (!file_exists(ROOT . "logs")) {
        shell_exec("mkdir " . ROOT . "logs");
    }
    if (!file_exists(ROOT . "logs")) {
        die("Folder " . ROOT . ".logs" . " does not exists");
    }
    $lokifile = ROOT . "logs/" . $lokifile;

    $file_open = fopen($lokifile, "a+");
    if ($file_open) {
        fwrite($file_open, "$time. $str \n");
        fclose($file_open);
    }
    return null;
}

function jsonError($str) {
    print_r(json_encode(array('error' => $str)));
}

function checkPost($s) {
// putsataan GET- ja POST-muuttujia haxor-yritysten varalta
    $etsi = array('#', '´', '%', '|', '--', '\t');
    $korv = array('&#35;', '&#39;', '&#37;', '&#124;', '&#150;', '&nbsp;');

    //$s = htmlspecialchars($s);
    $s = trim(str_replace($etsi, $korv, $s));
    $enc = mb_detect_encoding($s, 'UTF-8', true);
    return $s;
}

function checkNumber($int) {
    if (is_numeric($int)) {
        return $int;
    } else {
        return 0;
    }
}

function getConnect() {
    try {
        $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $db->exec("SET CHARACTER SET utf8");
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //Disables SQL injection
// Estää muutujan laittamisen suoraan kyselyyn
    } catch (PDOException $e) {
        print "PDO Error!: " . $e->getMessage() . "<br/>";
        die();
    }
    return $db;
}

/**
 * If needs to debug some parameters...
 */
function debug($text) {
    echo "<pre>\n";
    echo " **** DEBUG ***<br />";
    print_r($text);
    echo " <br />**** DEBUG ***";
    echo "</pre>\n";
}

/*
 * check is File_object_id sended by our software? if 0, its older ones and we can change it´s thumbnailboo to false
 */

function sendedByMassaSoftware($id, $objectId) {
    $return = 0;
    global $db;
    try {
        $sql = "SELECT file_object_id FROM tiedostot, listan_rivit WHERE listan_rivit.objektin_id = :objekti_id
                AND tiedostot.listan_rivi_id = listan_rivit.rivi_id AND tiedostot.file_object_id = :id";
        if (!$stmt = $db->prepare($sql)) {
            writeToLog("Cannot prepare stament: $sql");
            return -3;
        }

        $stmt->bindParam(":objekti_id", $objectId);
        $stmt->bindParam(":id", $id);
        if (!$stmt->execute()) {
            writeToLog("Cannot execute prepared statement for: $sql");
            return -2;
        }
        if ($stmt->rowCount() > 0) {
            $return = 1;
        } else {
            $return = 0;
        }
    } catch (Exception $error) {
        writeToLog($error->getMessage());
        return -1;
    }
    return $return;
}

function getCompletedRows($lista_id) {
    global $db;
    try {
        //$sql = "SELECT COUNT(*) FROM listan_rivit WHERE (valmis = 1 OR valmis = 2) AND lista_id = :lista_id";
        $sql = "SELECT COUNT(*) FROM listan_rivit WHERE valmis = 2 AND lista_id = :lista_id";
        if (!$stmt = $db->prepare($sql)) {
            writeToLog("Cannot prepare stament: $sql");
            return -3;
        }
        $stmt->bindParam(":lista_id", $lista_id);
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

function isReady($lista_id) {
    global $db;
    try {
        $sql = "SELECT min(valmis) as rivi_valmis, count(*) as maara, sum(valmis = 2) as valmiina, sum(valmis = 1) as kesken, sum(valmis = -1) as epaonnistuneet FROM listan_rivit WHERE lista_id = :lista_id";
        if (!$stmt = $db->prepare($sql)) {
            writeToLog("Cannot prepare stament: $sql");
            return -3;
        }
        $stmt->bindParam(":lista_id", $lista_id);
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

function sortDate($date) {
    $day = date('D', strtotime($date));
    $sorting = date("Ymd", strtotime($date));
    $color = "";
    switch ($day) {
        case "Mon":
            $day = "Ma";
            break;
        case "Tue":
            $day = "Ti";
            break;
        case "Wed":
            $day = "Ke";
            break;
        case "Thu":
            $day = "To";
            break;
        case "Fri":
            $day = "Pe";
            break;
        case "Sat":
            $day = "La";
            $color = "red";
            break;
        case "Sun":
            $day = "Su";
            $color = "red";
            break;
        default:
            $day = "";
    }
    return array("day" => $date, "week_day" => $day, "sort" => "<span class='hidden'>$sorting</span>", "color" => $color);
}
