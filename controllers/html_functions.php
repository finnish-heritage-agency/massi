<?php

function htmlStart($title = "", $version = "") {
    $msg = "<!DOCTYPE html>\n";
    $msg .= "<html lang='en'>\n";
    $msg .= "    <head>\n";
    $msg .= "        <meta charset='utf-8'>\n";
    $msg .= "        <meta http-equiv='X-UA-Compatible' content='IE=edge'>\n";
    $msg .= "        <meta name='viewport' content='width=device-width, initial-scale=1, shrink-to-fit=no'>\n";
    $msg .= "        <meta name='description' content=''>\n";
    $msg .= "        <meta name='author' content=''>\n";
    $msg .= "        <title> " . PREFIX . " - " . ucfirst($title) . "</title>\n";
    $msg .= "        <link href='" . WEBROOT . "/assets/css/all.min.css?v=$version' rel='stylesheet' type='text/css'>\n";
//    $msg .= "        <link href='https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i' rel='stylesheet'>\n";
    if (TEST_SERVER == false) {
        $msg .= "        <link href='" . WEBROOT . "/assets/css/sb-admin-2.min.css?v=$version' rel='stylesheet'>\n";
    } else {
        $msg .= "        <link href='" . WEBROOT . "/assets/css/sb-admin-2.css?v=$version' rel='stylesheet'>\n";
    }
    $msg .= "        <link href='" . WEBROOT . "/assets/css/changes.css?v=$version' rel='stylesheet'>\n";
    $msg .= "        <link href='" . WEBROOT . "/assets/css/datatables.bootstrap.css?v=$version' rel='stylesheet'>\n";
    $msg .= "        <link href='" . WEBROOT . "/assets/css/mpdf.css?v=$version' rel='stylesheet' media='mpdf'>\n";
    $msg .= "        <link href='" . WEBROOT . "/assets/css/dropify.min.css?v=$version' rel='stylesheet'>\n";
    $msg .= "   <script src='" . WEBROOT . "/assets/js/sweetAlert.min.js'></script>\n";
    $msg .= "<script>function Sweet(viesti, taso) {"
            . " swal.fire(viesti, '', taso ) }</script>\n";

    $msg .= "<script>function varmistus(era_id, collection_site = false) {
        Swal.fire({
  title: 'Varmista ennen siirron aloittamista, että kaikki objekteille siirrettävät tiedostot ovat siirtokansiossa: W-asema/Massadigitointi <br />" .
            "Massi käsittelee vain ne objektit, joille se löytää tiedostoja Massadigitointi-kansiosta. Samalle objektille voi siirtää tiedostoja vain yhden kerran.<br /><br />Haluatko varmasti aloittaa kuvien siirron?',
  showDenyButton: true,
  showCancelButton: false,
  confirmButtonText: `Kyllä`,
  denyButtonText: `Peruuta`,
  customClass: 'swal-wide',
}).then((result) => {
  /* Read more about isConfirmed, isDenied below */
  if (result.isConfirmed) {
    /* Swal.fire('Tallennettu', '', 'success'); */
    window.location='" . WEBROOT . "/sivu/erat/&merkitse_valmiiksi=1&valmis='+era_id+'&collection_site='+collection_site;
  } else if (result.isDenied) {
    Swal.fire('Tietoa ei päivitetty', '', 'info')
  }
})}</script>";
    $msg .= "    </head>\n";

    $msg .= "    <body id='page-top'>\n";
    $msg .= "       <div id='wrapper'>\n"; //<!-- Page Wrapper -->
    return $msg;
}

function htmlSidebar() {
    global $links;
    global $collection;
    $msg = "<ul class='navbar-nav bg-gradient-primary sidebar sidebar-dark accordion' id='accordionSidebar'>\n";

//    <!-- Sidebar - Brand -->
    $msg .= "    <a class='sidebar-brand d-flex align-items-center justify-content-center' href='" . WEBROOT . "'>\n";
    $msg .= "        <div class='sidebar-brand-icon rotate-n-15'>\n";
    $msg .= "            <i class='fas fa-file-image'></i>\n";
    $msg .= "        </div>\n";
    $msg .= "        <div class='sidebar-brand-text mx-3'>Massi</div>\n";
    $msg .= "    </a>\n";

//    <!-- Divider -->
    $msg .= "    <hr class='sidebar-divider my-0'>\n";

//    <!-- Nav Item - Dashboard -->
    /*
      $msg .= "    <li class='nav-item active'>\n";
      $msg .= "        <a class='nav-link' href='" . WEBROOT . "'>\n";
      $msg .= "            <i class='fas fa-fw fa-tachometer-alt'></i>\n";
      $msg .= "            <span>" . text("frontpage") . "</span></a>\n";
      $msg .= "    </li>\n";
     *
     */

//    <!-- Divider -->
    $msg .= "    <hr class='sidebar-divider'>\n";

//    <!-- Heading -->
    $msg .= "    <div class='sidebar-heading'>Linkit</div>\n";

//    <!-- Nav Item - Pages Collapse Menu -->
    /*
      $msg .= "    <li class='nav-item'>\n";
      $msg .= "        <a class='nav-link collapsed' href='#' data-toggle='collapse' data-target='#collapseTwo' aria-expanded='true' aria-controls='collapseTwo'>\n";
      $msg .= "            <i class='fas fa-fw fa-archive'></i><span>" . text("digitization batchs") . "</span></a>\n";
      $msg .= "        <div id='collapseTwo' class='collapse' aria-labelledby='headingTwo' data-parent='#accordionSidebar'>\n";
      $msg .= "            <div class='bg-white py-2 collapse-inner rounded'>\n";
      //    $msg .= "                <h6 class='collapse-header'>Custom Components:</h6>\n";

      foreach ($collection as $row) {
      $msg .= "        <a class='collapse-item' href='" . WEBROOT . "/sivu/" . $row["url"] . "/'>\n";
      $msg .= "            <i class='fas fa-fw " . $row["ico"] . "'></i>\n";
      $msg .= "            <span>" . ucfirst($row["name"]) . "</span></a>\n";
      }

      $msg .= "            </div>\n";
      $msg .= "        </div>\n";
      $msg .= "    </li>\n";
     *
     */
    foreach ($collection as $row) {
        $msg .= "    <li class='nav-item'>\n";
        if ($row["url"] == "uusiEra") {
            $msg .= "        <a class='nav-link' href='#view-modal-9999' data-toggle='modal' data-target='#view-modal-9999'>\n";
        } else {
            $msg .= "        <a class='nav-link' href='" . WEBROOT . "/sivu/" . $row["url"] . "/'>\n";
        }
        $msg .= "            <i class='fas fa-fw " . $row["ico"] . "'></i>\n";
        $msg .= "            <span>" . ucfirst($row["name"]) . "</span></a>\n";
        $msg .= "    </li>\n";
    }
    $tmp = callRest("POST", WEBROOT . "/rest/collections.php", array("getCollections" => 1), true);
    $count = 0;
    foreach ($tmp as $row) {
        $count++;
        if ($count > 5) {
            continue;
        }
        if ($row->rivi_valmis == 2) {
            $ico = "fa-check";
            $title = "Digitointierä on valmis";
        } else {
            $ico = "";
            $title = "Työnalla";
        }
        $msg .= "    <li class='nav-item link-padding'>\n";
        $msg .= "        <a class='nav-link' title='$title' href='" . WEBROOT . "/sivu/era/" . $row->lista_id . "/'>\n";
        $msg .= "            <i class='fas fa-fw $ico'></i>\n";
        $msg .= "            <span>" . $row->otsikko . "</span></a>\n";
        $msg .= "    </li>\n";
    }
    $msg .= "    <hr class='sidebar-divider d-none d-md-block'>\n";
    $msg .= "    <div class='sidebar-heading'>Ylläpito</div>\n";
    foreach ($links as $row) {
        $msg .= "    <li class='nav-item'>\n";
        $msg .= "        <a class='nav-link' href='" . WEBROOT . "/sivu/" . $row["url"] . "/'>\n";
        $msg .= "            <i class='fas fa-fw " . $row["ico"] . "'></i>\n";
        $msg .= "            <span>" . ucfirst($row["name"]) . "</span></a>\n";
        $msg .= "    </li>\n";
    }

//    <!-- Divider -->
    $msg .= "    <hr class='sidebar-divider d-none d-md-block'>\n";

//    <!-- Sidebar Toggler (Sidebar) -->
    $msg .= "    <div class='text-center d-none d-md-inline'>\n";
    $msg .= "        <button class='rounded-circle border-0' id='sidebarToggle'></button>\n";
    $msg .= "    </div>\n";

    $msg .= "</ul>\n";
    $msg .= "           <div id='content-wrapper' class='d-flex flex-column'>\n"; //<!-- Content Wrapper -->
    $msg .= "               <div id='content'>\n"; //<!-- Main Content -->
    $msg .= htmlTopBar();
    $msg .= "                <div class='container-fluid'>\n"; //<!-- Begin Page Content -->
    return $msg;
}

function htmlTopBar() {
    $msg = "            <nav class='navbar navbar-expand navbar-light bg-white topbar mb-4 shadow'>\n";
    if (TEST_SERVER == true) {
        $msg .= "<p class='text-danger'>Tämä on testiympäristö. Tiedostot päätyvät M+ testiympäristöön";
    } else {
        $msg .= "<p class='text-danger'>";
    }
    if (file_exists(SOFTWARE_BREAK)) {
        if (file_get_contents(SOFTWARE_BREAK) != "") {
            $msg .= " <br />Kokoelmatunnuksessa: " . file_get_contents(SOFTWARE_BREAK) . " on ongelmia. Tauko aloitettu tunnuksen takia. ";
        } else {
            $msg .= "<br />Siirtosovellus on tällä hetkellä tauolla. ";
        }
        $msg .= date("d.m.Y H:i:s", filemtime(SOFTWARE_BREAK));
        $msg .= "</p>";
    } else {
        $msg .= "</p>";
    }
    $msg .= "           <button id='sidebarToggleTop' class='btn btn-link d-md-none rounded-circle mr-3'>\n";
    $msg .= "           <i class='fa fa-bars'></i>\n";
    $msg .= "           </button>\n";
    $msg .= "           </nav>\n";

    return $msg;
}

function htmlStop($title = false) {
    $content = "<div class='form-group'>\n";
    $content .= "<label for='title' class='text-dark'>Erän nimi (pakollinen)</label>\n";
    $content .= "<input type='text' placeholder='Otsikko...' name='title' class = 'form-control col-md-12' required>";
    $content .= "<div class='invalid-feedback'>Kenttä ei saa olla tyhjä</div>\n";
    $content .= "</div>\n";
    $content .= showParsers();
    $content .= "<div class='form-group'>\n";
    $content .= "<label for='rows' class='text-dark'>Listaa objektinumerot (erottele välilyönnillä)</label>\n";
    $content .= "<input type='text' placeholder='Uudet rivit...' name='rows' class = 'form-control col-md-12' max='" . DATA_LENGTH . "'>";
    $content .= "</div>\n";
    $content .= "<p class='text-dark'>Tai lataa esinelista csv-tiedostona:<br />Excel --> Vie --> Muuta tiedostotyyppi --> CSV (luetteloerotin)</p>\n";
    $content .= "<input type='file' class='dropify' name='fileToUpload' id='fileToUpload'/>\n";
//$msg .= "        <!-- Bootstrap core JavaScript-->
    $msg = "       </div>\n"; //<!-- /.container-fluid stops -->
    $msg .= "       </div>\n"; //<!-- Page Wrapper stops-->
    $msg .= "       <footer class='sticky-footer bg-white'>\n";
    $msg .= "           <div class='container my-auto'>\n";
    $msg .= "               <div class='copyright text-center my-auto'>\n";
    $msg .= "                   <span>Copyright &copy; " . PREFIX . " " . date("Y", time()) . "</span>\n";
    $msg .= "               </div>\n";
    $msg .= "           </div>\n";
    $msg .= "       </footer>\n";
    $msg .= "       <a class='scroll-to-top rounded' href='#page-top'>\n";
    $msg .= "           <i class='fas fa-angle-up'></i>\n";
    $msg .= "       </a>\n";
    $msg .= "       </div>\n";
    $msg .= "   </div>\n";
    $modal = makeModalView("Uusi listaus", "new_collection", "Tuo objektilistaus", WEBROOT . "/sivu/uusiEra/", $content, "", "btn-success text-right");
    $msg .= "</div>" . $modal["message"];
    $msg .= "   <script src='" . WEBROOT . "/assets/js/jquery.min.js'></script>\n";
    $msg .= "   <script src='" . WEBROOT . "/assets/js/datatables.min.js'></script>\n";
    $msg .= "   <script src='" . WEBROOT . "/assets/js/bootstrap.bundle.min.js'></script>\n";
//        <!-- Core plugin JavaScript-->
    $msg .= "   <script src='" . WEBROOT . "/assets/js/jquery.easing.min.js'></script>\n";
//        <!-- Custom scripts for all pages-->
    $msg .= "   <script src='" . WEBROOT . "/assets/js/sb-admin-2.min.js'></script>\n";
//        <!-- Page level plugins -->
    $msg .= "   <script src='" . WEBROOT . "/assets/js/Chart.min.js'></script>\n";

    $msg .= "   <script src='" . WEBROOT . "/assets/js/datatables.bootstrap.min.js'></script>\n";
    $msg .= "   <script src='" . WEBROOT . "/assets/js/dropify.min.js'></script>\n";
    $msg .= "<script>$('.dropify').dropify({
           messages: {
        'default': 'Raahaa tiedosto tähän ruutuun tai klikkaa tästä.',
        'replace': 'Raahaa tiedosto tähän ruutuun tai klikkaa tästä.',
        'remove':  'Poista',
        'error':   'OOPS, ongelmia?'
        },
        tpl: {
        message:         '<div class=\"dropify-message\"><span class=\"fas fa-file-excel\" /> <h3>{{ default }}</h3></div>'
        }
            });</script>\n";
//        <!-- Page level custom scripts -->
//    $msg .= "        <script src='js/demo/chart-area-demo.js'></script>\n";
//    $msg .= "        <script src='js/demo/chart-pie-demo.js'></script>\n";
    $msg .= "<script>
        $(document).ready(function() {
            $('#dataTable').DataTable({
                'scrollX': true,
                'stateSave': true,
                'pageLength': 50,
                'language': { 'url': '/locales/fi_FI/finnish.json'},
                'order': [[ 0, 'desc' ]]
            });
        });</script>\n";
    $msg .= "<script>
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                // Fetch all the forms we want to apply custom Bootstrap validation styles to
                var forms = document.getElementsByClassName('needs-validation');
                // Loop over them and prevent submission
                var validation = Array.prototype.filter.call(forms, function (form) {
                    form.addEventListener('submit', function (event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        </script>\n";

    /*
      //Tämä hakee restin avulla erät välilehden tiedot
      $msg .= "<script>
      $(document).ready(function () {
      $('#erat').DataTable({
      'pageLength': 25,
      'stateSave': true,
      'order': [[0, 'desc']],
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'ajax': {
      'url': '" . WEBROOT . "/rest/collectionsDatatable.php'
      },
      'createdRow': function( row, data, dataIndex ) {
      if (data.valmis == 2) {
      $(row).addClass('bg-gradient-success' );
      $(row).addClass('text-gray-100' );
      $(row).removeClass( 'varmistus' );
      }
      if (data.valmis == 1){
      $(row).addClass( 'bg-gradient-warning' );
      $(row).addClass( 'text-gray-900' );
      }
      if (data.valmis === 0) {

      }
      if (data.valmis == -1) {
      $(row).addClass( 'bg-gradient-danger' );
      $(row).addClass( 'text-gray-900' );
      }
      if (data === false){
      $(row).removeClass( 'bg-gradient-warning' );
      $(row).removeClass( 'text-gray-200' );
      }
      },
      'columns': [
      {data: 'lista_id',
      render: function(data, type, row, meta){
      if(type === 'display'){
      data = '<a href=\"" . WEBROOT . "/sivu/era/' + data + '/\">' + data + '</a>';

      }
      return data;
      }
      },
      {data: 'otsikko'},
      {data: 'paivays'},
      {data: 'finna'},
      {data: 'maara'},
      {data: 'valmis',
      render: function(data, type, row, meta){
      if(data == 2){
      data = '<i class=\"fas fa-check\"></i> Digitointierä on valmis. ';
      }
      if(data == 1){
      data = '<i class=\"fas fa-hourglass-half\"></i> Digitointierä on kesken. ' ;
      }
      if(data == 0){
      data = '<i class=\"fas fa-hourglass-half\"></i> Digitointierää ei ole vielä aloitettu. ' ;
      data += '<button class=\"varmistus btn btn-sm btn-outline-success\" aria-label=\"\" onclick=\"varmistus(' + row.lista_id + ')\">Aloita tiedostojen siirto</button>';
      }
      if(data == -1){
      data = '<i class=\"fas fa-angry\"></i> Digitointierässä on virheitä.' ;
      }

      return data;
      }
      },
      ]
      });
      });
      </script>";
     */
    /*
      $msg .= "<script>
      $('#erat').on('click', 'tr ', function ()
      {
      // 'this' refers to the current <td>, if you need information out of it
      //window.open('http://example.com');
      console.log(this);
      });
      </script>";
     *
     */

    /*
      $msg .= "<script>function autoRefresh_div() { $('#all_jobs').load('" . WEBROOT . "/rest/checkAllJobs.php'); }
      autoRefresh_div();
      setInterval(autoRefresh_div, 5000);
      </script>\n";
     *
     */

    if ($title == "siirrot") {
        $msg .= "<script>
 (function checkJobs() {
                    $.ajax({
                        url: '" . WEBROOT . "/rest/checkAllJobs.php',
                        cache: false,
                        success: function (html) {
                            $('#all_jobs').html(html);
                        }
                    }).then(function() {
                        setTimeout(checkJobs, 20000);
                    });
                })();</script>";
    }
    if ($title == "era") {
        $msg .= "<script>
 (function checkJobs() {
         $('#loading').html('<img src=\"" . WEBROOT . "/img/loading.gif\"> Tarkistetaan ensimmäiset 100 käsittelemätöntä riviä ...');
            $.ajax({
                url: '" . WEBROOT . "/rest/checkOneJob.php?id=" . $_GET["id"] . "',
                cache: true,
                success: function (html) {
                    $('#one_job').html(html);
                    $('#loading').html(\"\");
                }
            }).then(function() {
                setTimeout(checkJobs, 20000);
            });
        })();</script>";
    }

    $msg .= "    </body>\n";
    $msg .= "</html>\n";
    return $msg;
}

function getUrlPage($url) {
    global $pages;
    ob_start();
    if (isset($pages [$url])) {
        require ($pages [$url]);
    } else {
        require ('error.php');
    }
    $site_code = ob_get_clean();
    return $site_code;
}

/**
 * Make a page card
 * @param int $size 1-12
 * @param string $title
 * @param string $text
 * @return html string
 */
function makeCard($size, $title, $text, $scrollable = true, $phase_bar = false) {
    $msg = "<div class='col-lg-$size mb-4 ";
    if ($scrollable == true) {
        $msg .= "scrollable";
    }

    $msg .= "'>\n";
    $msg .= "<div class='card shadow mb-4'>\n";
    $msg .= "    <div class='card-header py-3 no_pdf'>\n";
    $msg .= "        <h6 class='m-0 font-weight-bold text-primary'>$title</h6>\n";
    $msg .= "    </div>\n";
    if ($phase_bar != false) {
        $msg .= $phase_bar;
    }
    $msg .= "    <div class='card-body'>\n";
    $msg .= "        <p>$text</p>\n";
    $msg .= "    </div>\n";

    $msg .= "</div>\n";
    $msg .= "</div>\n";
    return $msg;
}

function makeModalView($title, $name, $value, $url, $content, $modal_size = "modal-lg", $design = "btn-success") {
    if ($name == "new_collection") {
        $rdm_id = 9999;
    } else {
        $rdm_id = rand(1, 100);
    }

    $message_button = "<button data-toggle='modal' data-target='#view-modal-$rdm_id'  id='modal-$rdm_id' class='btn $design'>$title</button>\n";
    $message = "\n<div id='view-modal-$rdm_id' class='modal fade hide' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true' style='display: none;'>\n";
    $message .= "    <div class='modal-dialog $modal_size'>\n";
    $message .= "        <div class='modal-content'>\n";
    $message .= "            <div class = 'modal-header'>$value\n";
    $message .= "               <button type='button' class='close' data-dismiss='modal' aria-label='Close' title='Sulje ikkuna'>\n";
    $message .= "                   <span aria-hidden='true'>&times;</span>\n";
    $message .= "               </button>\n";
    $message .= "            </div>\n";
    $message .= "            <div class='modal-body'>\n";
    $message .= "           <form class='form-group needs-validation' action='$url' method='post' novalidate autocomplete='off' enctype='multipart/form-data'>";
    $message .= $content;
    $message .= "            <div class='modal-footer'>\n";
    $message .= "               <input type = 'submit' class='btn btn-primary col-md-12' name='$name' value='$value' />\n";
    $message .= "            </div>\n";
    $message .= "            </form>\n";
    $message .= "        </div>\n";
    $message .= "    </div>\n";
    $message .= "   </div>\n";
    $message .= "</div>\n";
    return array("message" => $message, "button" => $message_button);
}

/**
 * just make popup alert on success or failed rest service
 * @return part of html
 */
function submitPopup() {
    $msg = "";
    if (isset($_SESSION["tallennusvirhe"]) || isset($_SESSION["tallennus_ok"])) {
        if (isset($_SESSION["tallennus_ok"]) && $_SESSION["tallennus_ok"] != "") {
            $msg .= "<script type=\"text/javascript\">Sweet('" . $_SESSION["tallennus_ok"] . "', 'success');</script>";
        } else {
            $msg .= "<script type=\"text/javascript\">Sweet('" . $_SESSION["tallennusvirhe"] . "', 'warning');</script>";
        }
        unset($_SESSION["tallennusvirhe"]);
        unset($_SESSION["tallennus_ok"]);
    }
    return $msg;
}

function breadCrumb($links, $choosed, $more_title = false) {
    $message = null;
    foreach ($links as $row) {
        $lisays = "";
        $class = "linkki";
        $nuolet = " -->";
        if ($row["title"] == $choosed) {
            $class = "valittu";
            $nuolet = "";
        }
        if (isset($row["lisays"])) {
            $nuolet = "";
            $lisays = $row["lisays"];
            $class = " pull-right nappula";
        }
        $message .= "  <a href='" . WEBROOT . $row["url"] . "' class='$class'>$lisays " . $row["title"] . "</a>$nuolet \n";
    }
    if ($more_title != "") {
        $message .= "<span class='bread_title'>$more_title</span>";
    }

    return $message;
}

/**
 *
 * @param type $paivays Päiväys muotoon d.m.Y
 * @return array (paivays, viikonpäivä, sorttaus, korostus)
 * Sorttausta voi käyttää taulukon lajitteluun päiväyksen mukaan
 *          <td>" . $paivaystiedot["sorttaus"] . " ja pvm yms. </td>
 * Korostus on millä maalataan esim. koko taulun rivi. Viikonloppu ja pyhäpäivät
 * https://pear.php.net/package/Date_Holidays_Finland/
 */
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

/**
 *
 * @param array $tmp
 *  [id] => 4
 *  [teksti] => lisenssi1
 *  [sisainen_nimi] => sisainen
 *  [aktiivinen] => 0
 * @param string $id
 * @param string $name
 * @param string $submit if false, submit button disappear
 * @param int $size
 * @param boolean $only_actives = display only actived rows
 * @param string label. Label text
 * @return html string
 */
function makeDropDownFromArray($tmp, $id, $name, $submit = false, $size = 9, $label = false, $only_actives = false, $site = "", $value = null) {
    $required = null;
    $message = "";
    if ($site == "settings") {
        $message .= "<div class='form-row'>\n";
        $message .= "       <div class='form-group col-md-4'>\n";
        $message .= "       <label for='label'>$label</label>\n";
        $message .= "   </div>\n";
        $message .= "       <div class='form-group col-md-$size'>\n";
    }
    if ($site == "new_era") {
        $message .= "       <div class='form-group col-md-$size'>\n";
        $message .= "       <label for='label'>$label</label>\n";
    }

    if ($name == "artist_id") {
//        $required = "required"; //Jos halutaan pakolliseksi
        $no_value = null;
    } else {
        $no_value = 0;
    }
    $message .= "       <select id='$id' name='$name' class='form-control' $required>\n";
    $message .= "       <option selected value='$no_value'>" . text("choose") . "...</option>\n";
    if (is_array($tmp) && count($tmp) > 0) {
        foreach ($tmp as $row) {
            $selected = null;
            $no_active = "";
            if ($row->aktiivinen == 0) {
                if ($only_actives == true) {
                    continue;
                }
                $no_active = "disabled";
            }
            if ($value == $row->sisainen_nimi) {
                $selected = "selected";
            }
            $message .= "       <option value='" . $row->id . "' class='$no_active' $selected>" . $row->teksti . " (" . $row->sisainen_nimi . ")</option>\n";
        }
    }
    $message .= "       </select>\n";
    $message .= "   </div>\n";
    if ($submit != false) {
        $button_size = 12 - $size;
        $message .= "   <div class='col-md-$button_size'>\n";
//        $message .= "       <button type='submit' class='btn btn-primary' name='$submit'>" . text("change status") . "</button>\n";
        $message .= "       <button type='submit' class='btn btn-danger text-right' name='$submit'>" . text("remove") . "</button>\n";
        $message .= "   </div>\n";
    }
    if ($site == "settings") {
        $message .= "   </div>\n";
    }

    return $message;
}

function showParsers() {
    $content = "<p class='text-dark'>Jäsentäjänä käy seuraavat merkit:<ul>";
    foreach (PARSER as $parser) {
        $valeja = substr_count($parser, " ");
        if ($valeja > 0) {
            $content .= "<li class='text-dark'>välilyöntejä peräkkäin: $valeja kpl</li>";
        } else {
            $content .= "<li class='text-dark'>$parser</li>";
        }
    }
    $content .= "</ul></p>\n";
    return $content;
}

/**
 * Check is file csv and size below 1 mt.
 * @param $_FILES $file
 * @return int
 */
function checkFile($file) {
    if ($file["fileToUpload"]["tmp_name"] == "") {
        return null;
    }
    $type = pathinfo($file["fileToUpload"]["name"], PATHINFO_EXTENSION);
    $size = $file ["fileToUpload"] ["size"];

    if ($type != "csv") {
        $viesti = "Seuraavat tiedostomuodot on sallittuja (csv)";
        $_SESSION["tallennusvirhe"] = $viesti;
        return -1;
    }
    if ($size >= 10000) {
        $viesti = "Tiedoston maksimikoko on 1 mt.";
        $_SESSION["tallennusvirhe"] = $viesti;
        return -2;
    }
    return 1;
}
