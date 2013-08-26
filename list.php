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

            ////////////////
            // PROCESSING //
            ////////////////
            $result = $database->query("SELECT * FROM ucastnici WHERE si_cip_zavodni IS NOT NULL ORDER BY start_cislo;");

            echo "<table border='1' cellpadding='0' cellspacing='0' width='850'>";
            while ($ucastnik = $result->fetch_object())
            {
                $vys['start_cislo'] = $ucastnik->start_cislo;
                $vys['kategorie'] = $ucastnik->kategorie;
                $vys['jmeno'] = $ucastnik->a_jmeno . " " . $ucastnik->a_prijmeni;
                if ($ucastnik->b_jmeno || $ucastnik->b_prijmeni) $vys['jmeno'] .= " / " . $ucastnik->b_jmeno . " " . $ucastnik->b_prijmeni;
                if ($ucastnik->tym <> '') $vys['jmeno'] .= " [{$ucastnik->tym}]";

                echo "<tr>";
                echo "<td align='center' width='80'><font size='7'>{$vys['start_cislo']}</font></td>";
                echo "<td align='center' width='40'><font size='7'>{$vys['kategorie']}</font></td>";
                echo "<td width='450'><font size='5'>{$vys['jmeno']}</font></td>";
                echo "</tr>";
                unset($vys);
            }
            echo "</table>";
        ?>
    </body>
</html>
