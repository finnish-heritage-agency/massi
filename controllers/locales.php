<?php

/* * 29.7.2020
 * Pistetty mukaan, mutta ei varsinaisesti käytetä missään
 */
$lang = 'fi_FI';
if (isset($_GET['lang']) && valid($_GET['lang'])) {
// the locale can be changed through the query-string
    $lang = $_GET['lang'];    //you should sanitize this!
    setcookie('lang', $lang); //it's stored in a cookie so it can be reused
} elseif (isset($_COOKIE['lang']) && valid($_COOKIE['lang'])) {
// if the cookie is present instead, let's just keep it
    $lang = $_COOKIE['lang']; //you should sanitize this!
} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
// default: look for the languages the browser says the user accepts
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    array_walk($langs, function (&$lang) {
        $lang = strtr(strtok($lang, ';'), ['-' => '_']);
    });
    foreach ($langs as $browser_lang) {
        if (valid($browser_lang)) {
            $lang = $browser_lang;
            break;
        }
    }
}

// here we define the global system locale given the found language
putenv("LANG=$lang");
setlocale(LC_ALL, $lang);

bindtextdomain('ui', './locales');
bind_textdomain_codeset('ui', 'UTF-8');
textdomain('ui');

