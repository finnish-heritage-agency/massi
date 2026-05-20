<?php

/**
 * Testiympäristön asetukset. Tämä ei ole mukana GIT:ssä
 */
/*
 * STATUS
 * 9 = käsittelyssä
 * 2 = valmis
 * 1 = seuraavaksi jonossa
 * 0 = aloittamatta
 *
 */
define("WEBROOT", "");

/* SOFTWARE settings */
define("TEST_SERVER", true);
define("APIKEY", "");
define("HTTPS_ONLY", false);
define("JOB_PHASES", array(1 => "tarkistus", 2 => "metatiedot", 3 => "lahetys", 4 => "nayttokuvat")); //Muista laittaa tietokantaan samat rivit jos haluat lisää vaiheita...
//define("JOB_PHASES", array(1 => "tarkistus", 2 => "metatiedot", 3 => "lahetys")); //Muista laittaa tietokantaan samat rivit jos haluat lisää vaiheita...
define("DATA_LENGTH", 10000); //tarkistetaan tulevan taulun datan pituus
define("MUSEUM_DOMAIN", "");
define("PREFIX", "");
define("PARSER", array(" ", "|"));
define("LOGS", ROOT . "logs/"); //Store all log files
define("CRON_FOLDER", ROOT . "cron/");
define("ROOT_USER","");
define("SAVE_LOGS", "14"); //days
define("BY_DIGITOINTIERA", true); //Museovirasto ei halua käsitellä digitointieriä rivikerrallaan //True, hyväksytään koko digitointierä, false, hyväksytään rivi kerrallaan
define("SEND_TIME", "06:00 - 22:00"); //Milloin tiedostoja lähetetään M+ järjestelmään
define("RE_TRIES", 2); //Pakollinen! Kuinka monta kertaa yritetään tehdä sama lähetys
define("PICTURE_FOLDER", "/kuvat/"); //NFS/SMB folder where all new data is stored
define("REMOVE_READY_FOLDER", true); //IF true, remove files wich has been succesfully moved in M+
define("EXIFTOOL", "/usr/local/bin/exiftool");
define("SHOW_SUCCESS_LOGS", false);
define("SOFTWARE_BREAK", __DIR__ . "/software_break");
define("MAX_ATTEMPTS", 10);

/**
 * DB settings
 */
define("DB_NAME", "");
define("DB_HOST", "");
define("DB_USER", "");
define("DB_PASSWORD", "");
/*
 * MUSEUM+ settings
 */
define("M_USERNAME", "");
define("M_PASSWORD", "");
//define("M_URL", ""); //Tuotanto
define("M_URL", ""); //Testi
define("CACHE_LIFETIME", 240); //Password cache lifetime
define("TMP", ROOT . "../../tmp/"); //M+ key

