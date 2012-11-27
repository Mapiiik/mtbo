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
            if (isset($_GET['card']))
            {
                $read_card_id = $_GET['card'];
            }
            else
            {
                $result = $databaseSI->query("SELECT stamp_card_id FROM stamps ORDER BY id_stamp DESC LIMIT 1;");
                $read_card_id = $result->fetch_object();
                $read_card_id = $read_card_id->stamp_card_id;
                echo $databaseSI->error;
            }
            
            $result = $database->query("SELECT * FROM ucastnici WHERE si_cip_zavodni = $read_card_id;");
            if (($count = $database->affected_rows) > 1) echo 'PROBLEM - MULTIPLE RECORDS !!!';
            //echo $count;
            $ucastnik = $result->fetch_object();
            //var_dump($line);
            echo $database->error;

            echo "Team: " . $ucastnik->tym . "<br />";

            echo "Jméno: " . $ucastnik->a_jmeno . " " . $ucastnik->a_prijmeni;
            if ($ucastnik->b_jmeno || $ucastnik->b_prijmeni) echo " / " . $ucastnik->b_jmeno . " " . $ucastnik->b_prijmeni;
            echo "<br />";
            
            echo "Kategorie: " . $ucastnik->kategorie . "<br />";
            
            echo "Start. číslo: " . $ucastnik->start_cislo . "<br />";

            echo "SI code: " . $ucastnik->si_cip_zavodni . "<br />";

            echo "Start: " . $ucastnik->start_cas . "<br /><br />";
            
            $carddata = new SportIdent($ucastnik->si_cip_zavodni);
            $carddata->loadStampsFromDB();
            $carddata->loadPointsFromDB();
            
            echo "<table border='1' cellpadding='0' cellspacing='0'>";
            echo "<tr>";
            echo "<th>kontrola</th>";
            echo "<th>mezičas</th>";
            echo "<th>celk.čas</th>";
            echo "<th>body</th>";
            echo "<th>celkem</th>";
            echo "</tr>";
            
            $lasttime = $ucastnik->start_cas;
            
            foreach ($carddata->stamps as $stamp)
            {
                if ($stamp['stamp_control_code'] >= 30)
                {
                    echo "<tr>";
                    echo "<td align='center'>{$stamp['stamp_control_code']}</td>";
                    $mezicas = get_time_difference($lasttime, $stamp['stamp_punch_datetime']);
                    $mezicasf = sprintf( '%02d:%02d:%02d', $mezicas['hours'], $mezicas['minutes'], $mezicas['seconds']);
                    echo "<td align='center'>{$mezicasf}</td>";
                    $celkcas = get_time_difference($ucastnik->start_cas, $stamp['stamp_punch_datetime']);
                    $celkcasf = sprintf( '%02d:%02d:%02d', $celkcas['hours'], $celkcas['minutes'], $celkcas['seconds']);
                    echo "<td align='center'>{$celkcasf}</td>";
                    $lasttime = $stamp['stamp_punch_datetime'];
                    echo "<td align='center'>{$carddata->points[$stamp['stamp_control_code']]}</td>";
                    $points += $carddata->points[$stamp['stamp_control_code']];
                    echo "<td align='center'>{$points}</td>";
                    echo "</tr>";
                }
                if ($stamp['stamp_control_code'] == 0)
                {
                    echo "<tr>";
                    echo "<td align='center'><b>CÍL</b></td>";
                    $mezicas = get_time_difference($lasttime, $stamp['stamp_punch_datetime']);
                    $mezicasf = sprintf( '%02d:%02d:%02d', $mezicas['hours'], $mezicas['minutes'], $mezicas['seconds']);
                    echo "<td align='center'>{$mezicasf}</td>";
                    $celkcas = get_time_difference($ucastnik->start_cas, $stamp['stamp_punch_datetime']);
                    $celkcasf = sprintf( '%02d:%02d:%02d', $celkcas['hours'], $celkcas['minutes'], $celkcas['seconds']);
                    echo "<td align='center'><b>{$celkcasf}</b></td>";
                    $lasttime = $stamp['stamp_punch_datetime'];
                    echo "<td align='center'>{$carddata->points[$stamp['stamp_control_code']]}</td>";
                    $points += $carddata->points[$stamp['stamp_control_code']];
                    echo "<td align='center'><b>{$points}</b></td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
            echo "<br />";
            
            echo "Penalizace: ";
            if ($celkcas['hours'] >= 6) echo 'DISKVALIFIKACE';
            else if ($celkcas['hours'] >= 5)
            {
                echo ($celkcas['minutes'] * 5) . " bodů";
                echo "<br /><br />";
                echo '<b>Celkem MTBO bodů: ' . ($carddata->points() - ($celkcas['minutes'] * 5)) . "</b>";
            }
            else
            {
                echo "0 bodů";
                echo "<br /><br />";
                echo '<b>Celkem MTBO bodů: ' . $carddata->points() . "</b>";
            }
            
            if ($ucastnik->si_cip == 0) echo "<br /><br /><b>PROSÍM VRAŤTE ČIP, DĚKUJEME :-)</b>";
        ?>
    </body>
</html>
