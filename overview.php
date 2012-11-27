<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=UTF-8");

require_once ('cfg/config.php');
require_once ('classes/database.class.php');
require_once ('classes/sportident.class.php');
require_once ('functions/common.func.php');

error_reporting(E_ALL ^ E_NOTICE);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <?php
            // Connect database
            $database = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
            if ($database->connect_error) {
                die('Connect Error (' . $database->connect_errno . ') '
                        . $database->connect_error);
            }
            $database->set_charset('utf8');

            // Connect SI database
            $databaseSI = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE_SI);
            if ($database->connect_error) {
                die('Connect Error (' . $database->connect_errno . ') '
                        . $database->connect_error);
            }
            $databaseSI->set_charset('utf8');

            ////////////////
            // PROCESSING //
            ////////////////
            if ($_GET['limit']) $limit = $_GET['limit'];
            
            $result = $database->query("SELECT * FROM ucastnici WHERE si_cip_zavodni IS NOT NULL;");

            while ($ucastnik = $result->fetch_object())
            {
                echo $database->error;
                $vys['start_cislo'] = $ucastnik->start_cislo;
                $vys['jmeno'] = $ucastnik->a_jmeno . " " . $ucastnik->a_prijmeni;
                if ($ucastnik->b_jmeno || $ucastnik->b_prijmeni) $vys['jmeno'] .= " / " . $ucastnik->b_jmeno . " " . $ucastnik->b_prijmeni;
                if ($ucastnik->tym <> '') $vys['jmeno'] .= " [{$ucastnik->tym}]";

                $carddata = new SportIdent($ucastnik->si_cip_zavodni);
                $carddata->loadStampsFromDB();
                $carddata->loadPointsFromDB();

                $cil = array_pop($carddata->stamps);
                $celkcas = @get_time_difference($ucastnik->start_cas, $cil['stamp_punch_datetime']);
                
                $vys['cas'] = strtotime(sprintf( '%02d:%02d:%02d', $celkcas['hours'], $celkcas['minutes'], $celkcas['seconds']));
                $vys['body_mtbo'] = $carddata->points();

                $result_vyjezd = $database->query("SELECT * FROM vyjezdy WHERE start_cislo = {$ucastnik->start_cislo};");
                $vyjezd = $result_vyjezd->fetch_object();
                echo $database->error;
                $vys['body_mtb'] = max(array($vyjezd->a1,$vyjezd->a2)) + max(array($vyjezd->b1,$vyjezd->b2));

                if ($celkcas['hours'] >= 6)
                {
                    $vys['penalizace'] = NULL;
                }
                else if ($celkcas['hours'] >= 5)
                {
                    $vys['penalizace'] = $celkcas['minutes'] * 5;
                }
                else
                {
                    $vys['penalizace'] = 0;
                }
                if ($vys['cas'] == 1313186400) $vys['penalizace'] = NULL;
                
                if (is_null($vys['penalizace']))
                {
                    $vys['body_celkem'] = NULL;
                }
                else
                {
                    $vys['body_celkem'] = $vys['body_mtbo'] + $vys['body_mtb'] - $vys['penalizace'];
                }
                
                $vysledky[$ucastnik->kategorie][] = $vys;
                unset($vys);
            }

            foreach ($vysledky as $skupina => $vysledky_skupiny)
            {
                foreach ($vysledky_skupiny as $key => $value) {
                    $cas[$key] = $value['cas'];
                    $body[$key] = $value['body_celkem'];
                }
                array_multisort($body, SORT_DESC, $cas, SORT_ASC, $vysledky_skupiny);
                unset($cas);
                unset($body);
                $vysledky[$skupina] = $vysledky_skupiny;
            }
            
            //var_dump($vysledky);

            foreach ($vysledky as $kategorie => $vysledky_kategorie)
            {
                echo "<table border='1' cellpadding='0' cellspacing='0' width='850'>";
                echo "<tr>";
                echo "<th>pořadí</th>";
                echo "<th>startovní číslo</th>";
                echo "<th>kategorie</th>";
                echo "<th>jméno</th>";
                echo "<th>čas</th>";
                echo "<th>body MTBO</th>";
                echo "<th>body MTB</th>";
                echo "<th>penalizace</th>";
                echo "<th>body celkem</th>";
                echo "</tr>";

                $poradi = 1;
                foreach ($vysledky_kategorie as $vys)
                {
                    if (isset($limit) && ($poradi > $limit)) continue;
                    echo "<tr>";
                    echo "<td>";
                        if (!is_null($vys['body_celkem'])) echo $poradi++;
                    echo "</td>";
                    echo "<td align='center'>{$vys['start_cislo']}</td>";
                    echo "<td align='center'>{$kategorie}</td>";
                    echo "<td width='450'>{$vys['jmeno']}</td>";
                    echo "<td align='center'>" . date('H:i:s', $vys['cas']) . "</td>";
                    echo "<td align='center'>{$vys['body_mtbo']}</td>";
                    echo "<td align='center'>{$vys['body_mtb']}</td>";
                    echo "<td align='center'>{$vys['penalizace']}</td>";
                    echo "<td align='center'>";
                        if (!is_null($vys['body_celkem']))
                            echo $vys['body_celkem'];
                        else
                            echo 'DIS';
                    echo "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                echo "<br />";
            }
        ?>
    </body>
</html>
