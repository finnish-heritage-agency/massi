<?php

function timerStart() {
    global $tstart;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime [1] + $mtime [0];
    $tstart = $mtime;
}

/**
 * Shows the laptime:
 * echo lapTime();
 */
function lapTime() {
    global $tstart;
    $mtime = microtime();
    $mtime = explode(" ", $mtime);
    $mtime = $mtime [1] + $mtime [0];
    $tend = $mtime;
    $tpassed = ($tend - $tstart);
    return round($tpassed, 2) . " sec";
}

/**
 *
 * @param string $text
 * @param string $moodi
 * @param boolean $clear
 * @param boolean $desc
 * @param boolean $error true => error, false => success
 * @return type
 */
function writeLog($text, $moodi = null, $error = true) {
    if (strlen($text) < 5) {
        return null;
    }
    if (SHOW_SUCCESS_LOGS == false && $error == false) {
        $moodi = "success_$moodi";
    }
    if ($moodi == null) {
        $text = utf8_decode($text);
        $lokifile = "debug-log";
    } else {
        $lokifile = $moodi . "-log";
    }

    if (!file_exists(LOGS)) {
        shell_exec("mkdir " . LOGS);
    }
    if (!file_exists(LOGS)) {
        die("Folder " . LOGS . " does not exists");
    }
    $lokifile = LOGS . $lokifile;
    $file_open = fopen($lokifile, "a+");
    if ($file_open) {
        fwrite($file_open, date("H:i:s", time()) . ". $text");
        fclose($file_open);
    }
    return null;
}

function showErrors($number) {
    error_reporting(E_ALL | E_STRICT);
    ini_set("display_errors", "$number");
}

/**
 * If needs to debug some parameters...
 */
function debug($text) {
    echo "<pre>\n";
    echo " **** DEBUG *** <br />";
    print_r($text);
    echo " <br />**** DEBUG ***";
    echo "</pre>\n";
}

//https://weichie.com/blog/curl-api-calls-with-php/
/**
 * Using on REST service
 * @param string $method
 * @param string $url
 * @param array $data
 * @param boolean decode
 * @param boolean retry
 * @return json/array
 */
function callRest($method, $url, $data, $decode = false, $retry = false) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_TIMEOUT, 3600);
    switch (strtoupper($method)) {
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;
        default:
            if ($data) {
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "APIKEY: " . APIKEY,
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    // EXECUTE:
    $result = curl_exec($curl);
    if (!$result && $result != 0) {
        echo("Connection Failure: $url | METHOD: $method. Sleeping... 10 sec");
        if ($result == false) {
            print_r($data, true);
            sleep(30);
            $result = callRest($method, $url, $data, $decode, true);
        }
    }
    curl_close($curl);
    if ($decode == true) {
        return json_decode($result);
    }
    return $result;
}

/**
 *
 * @param type $text
 * @param type $text_mode = 1 ucfirst, 2 = upper the whole word
 * @return type
 */
function text($text, $text_mode = 1) {
    if ($text_mode == 1) {
        return ucfirst(gettext($text));
    } elseif ($text_mode == 2) {
        return mb_strtoupper(gettext($text));
    } elseif ($text_mode == 3) {
        return mb_strtolower(gettext($text));
    }
    return gettext($text);
}

/**
 * Verifies if the given $locale is supported in the project
 * @param string $locale
 * @return bool
 */
function valid($locale) {
    return in_array($locale, ['en', 'swe', 'fi_FI']);
}

function checkPost($s) {
// putsataan GET- ja POST-muuttujia haxor-yritysten varalta
    $etsi = array('#', '´', '%', '|', '--', '\t');
    $korv = array('&#35;', '&#39;', '&#37;', '&#124;', '&#150;', '&nbsp;');

    //$s = htmlspecialchars($s);
    $s = trim(str_replace($etsi, $korv, $s));
    $enc = mb_detect_encoding($s, 'UTF-8', true);
    if ($enc == 'UTF-8') {
        return htmlentities($s);
    } else {
        return htmlentities(utf8_encode($s));
    }
}

function checkNumber($int) {
    if (is_numeric($int)) {
        return $int;
    } else {
        return 0;
    }
}

function changeReturn($number) {
    $array = array(1 => "Kyllä", 0 => "Ei");
    return $array[$number];
}

/**
 * Museoviraston mukaisesti määritetyt tyypit
 * Määritys settings.php
 * @global array $types
 * @param text $ext
 * @return text
 */
function getExtensionType($ext) {
    global $types;
    foreach ($types as $just => $values) {
        if (in_array($ext, $values)) {
            foreach ($GLOBALS as $varName => $value) {
                if ($value === $values) {
                    return $just;
                }
            }
        }
    }
    return null;
}

/**
 * if print == false, show icons
 * @param int $phase
 * @param boolean $print
 * @return string
 */
function showIcon($phase, $print = false, $link = null) {
    if ($phase == 9 || $phase == 99) {
        //$status= "<div class='spinner-border spinner-border-sm' role='status' title='$phase'><span class='sr-only'>Loading...</span></div>\n";
        $status = "<span class='badge badge-default' alt='Käsittelyssä' title='Käsittelyssä'><i class='fas fa-hourglass-half fa-spin fa-fw'> </i></span>\n";
        if ($print == true) {
            $status = "Käsittelyssä";
        }
    } elseif ($phase == 98) {
        $status = "<span class='badge badge-default' alt='Viety toisessa erässä. Ei viedä enää uudestaan.' title='Viety toisessa erässä. Ei viedä enää uudestaan.'><i class='fas fa-sync-alt'></i></span>\n";
        //$status = "<span class='badge badge-default' alt='toisessa erässä' title='Toisessa erässä'><i class='fas fa-sync-alt fa-spin fa-fw'></i></span>\n";
        if ($print == true) {
            $status = "Ei käsitellä";
        }
    } elseif ($phase == 2) {
        $status = "<span class='badge badge-success' alt='OK' title='OK'><i class='fas fa-check'></i></span>\n";
        if ($print == true) {
            $status = "OK";
        }
    } elseif ($phase == 1) {
        $status = "<span class='badge badge-default' alt='Käsittelyssä' title='Käsittelyssä'><i class='fas fa-hourglass fa-spin fa-fw'></i></span>\n";
        if ($print == true) {
            $status = "Käsittelyssä";
        }
    } elseif ($phase < 0) {
        $status = "$link<span class='badge badge-danger' alt='Virhe' title='Virhe'><i class='fas fa-exclamation'></i></span></a>\n";

        if ($print == true) {
            $status = "Virhe";
        }
    } else {
        $status = "<span class='badge badge-default' alt='Aloittamatta' title='Aloittamatta'><i class='fas fa-hourglass-start'></i></span>\n";
        if ($print == true) {
            $status = "Aloittamatta";
        }
    }
    return $status;
}

function checkMPlusStatus() {
    if (file_exists(SOFTWARE_BREAK)) {
        return "Sovelluksen katko on käynnissä. Lisätietoja palvelun vastuuhenkilöltä.";
    }
    $mplusMaintenance = clockBetween("03:50", "04:30");
    if ($mplusMaintenance == 1) {
        return "M+ tuotannon uudelleenkäynnistys";
    }
    $tmp = callRest("POST", WEBROOT . "/rest/checkMplusOnline.php", array("checkMPlus" => 1), true);
    if ($tmp != "OK") {
        sleep(600); //Rajapinta alhaalla, odotetaan 10 minuuttia
        return "M+ yhteysongelma: $tmp";
    } else {
        return $tmp;
    }
}

/**
 * Onko kello näiden kahden ajan välissä. Jos on, niin palautetaan 1, muussa tapauksessa 0
 * @return 0 1
 */
function clockBetween($begin = null, $end = null) {
    $input = date("H:i", time());
    if ($begin == null) {
        $time = SEND_TIME;
        $tmp = explode(" - ", SEND_TIME);
        if (!isset($tmp[1])) {
            $viesti = "Ei saada parsittua aikaa " . SEND_TIME . "\n";
            return 0;
        }
        $begin = $tmp[0];
        $end = $tmp[1];
    }
//    echo "Ajankohta: $begin -> $end. Kello: $input\n";
    $f = DateTime::createFromFormat('!H:i', $begin);
    $t = DateTime::createFromFormat('!H:i', $end);
    $i = DateTime::createFromFormat('!H:i', $input);
    if ($f > $t) {
        $t->modify('+1 day');
    }
    if (($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t)) {
        return 1;
    } else {
        return 0;
    }
}

function str_lreplace($search, $replace, $subject) {
    $pos = strrpos($subject, $search);

    if ($pos !== false) {
        $subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
}
