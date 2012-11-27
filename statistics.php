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
            
            $result = $databaseSI->query("SELECT stamp_card_id, stamp_control_code FROM `stamps` GROUP BY stamp_card_id, stamp_control_code ORDER BY stamp_control_code;");

            while ($kontrola = $result->fetch_object())
            {
                echo $database->error;
                $kontroly[$kontrola->stamp_control_code]++;
            }
            echo "<table border='1' cellpadding='0' cellspacing='0' width='850'>";
            echo "<tr>";
            echo "<th>kontrola</th>";
            echo "<th>počet návštěv</th>";
            echo "</tr>";

            foreach ($kontroly as $kontrola_id => $kontrola_pocet)
            {
                echo "<tr>";
                echo "<td align='center'>{$kontrola_id}</td>";
                echo "<td align='center'>{$kontrola_pocet}</td>";
                echo "</tr>";
            }
            echo "</table>";
            echo "<br />";
        ?>
    </body>
</html>
