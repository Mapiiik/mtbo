<?php
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/html; charset=UTF-8");

require_once ('cfg/config.php');
require_once ('classes/database.class.php');

error_reporting(E_ALL ^ E_NOTICE);
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
        <script type="text/javascript" language="javascript">// <![CDATA[
        function checkAll(formname, checktoggle)
        {
          var checkboxes = new Array(); 
          checkboxes = document[formname].getElementsByTagName('input');

          for (var i=0; i<checkboxes.length; i++)  {
            if (checkboxes[i].type == 'checkbox')   {
              checkboxes[i].checked = checktoggle;
            }
          }
        }
        // ]]></script>
    </head>
    <body>

        	<form action="" method="post" enctype="multipart/form-data" name="readoutform">  
		<span>
                        <?php
                        if (isset($_POST['manual-readout']) && ($_POST['manual-readout'] == 'readout'))
                        {
                            $database_ucastnici = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
                            if ($database_ucastnici->connect_error) {
                                die('Connect Error (' . $database_ucastnici->connect_errno . ') '
                                        . $database_ucastnici->connect_error);
                            }
                            $database_ucastnici->set_charset('utf8');

                            if (!$result = $database_ucastnici->query("UPDATE ucastnici SET start_cas = '" . $_POST['datum'] . ' ' . $_POST['start_cas'] . "' WHERE si_cip_zavodni =  " . $_POST['cislo_cipu'] . ";"))
                            {
                                echo $database->error;
                                die();
                            }

                            
                            $database = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE_SI);
                            if ($database->connect_error) {
                                die('Connect Error (' . $database->connect_errno . ') '
                                        . $database->connect_error);
                            }
                            $database->set_charset('utf8');

                            if (!$result = $database->query("INSERT INTO lccards (id_event, card_id, card_readout_datetime) VALUES (0, " . $_POST['cislo_cipu'] . ", NOW());"))
                            {
                                echo $database->error;
                                die();
                            }
                            
                            for ($ik = 0; $ik < 100; $ik++)
                            {
                                if (isset($_POST['check_' . $ik]) && is_numeric($_POST['check_' . $ik]))
                                {
                                    if (!$result = $database->query("INSERT INTO stamps (id_event, stamp_card_id, stamp_control_code, stamp_control_mode, stamp_readout_datetime, stamp_punch_datetime) VALUES (0, " . $_POST['cislo_cipu'] . ", " . $_POST['check_' . $ik] . ", 1, NOW(), '" . $_POST['datum'] . ' ' . $_POST['cil_cas'] . "');"))
                                    {
                                        echo $database->error;
                                        die();
                                    }
                                }
                            }
                            
                            if (!$result = $database->query("INSERT INTO stamps (id_event, stamp_card_id, stamp_control_code, stamp_control_mode, stamp_readout_datetime, stamp_punch_datetime) VALUES (0, " . $_POST['cislo_cipu'] . ", 0, 4, NOW(), '" . $_POST['datum'] . ' ' . $_POST['cil_cas'] . "');"))
                            {
                                echo $database->error;
                                die();
                            }
                            echo 'Load OK';

                        }
                        ?>
                    
			<strong>Načíst data závodu</strong><br /> 
			<input type="hidden" name="manual-readout" value="readout"/> 
			Číslo čipu:<input type="text" name="cislo_cipu" value=""/><br />  
			Datum:<input type="text" name="datum" value="<?php echo date('Y-m-d') ?>"/><br />  
			Startovní čas:<input type="text" name="start_cas" value=""/><br />  
			Cílový čas:<input type="text" name="cil_cas" value=""/><br />  
                        <br />
                        <table border="1" width="0" style="border: 1px solid black">
                        <?php
                            $database = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
                            if ($database->connect_error) {
                                die('Connect Error (' . $database->connect_errno . ') '
                                        . $database->connect_error);
                            }
                            $database->set_charset('utf8');

                            $result = $database->query("SELECT cislo, body FROM kontroly ORDER BY cislo;");
                            $im = 0;
                            echo '<tr>';
                            while ($line = $result->fetch_assoc())
                            {
                                if (($im == 10) || ($im == 20) || ($im == 30) || ($im == 40) || ($im == 50)) echo '</tr/><tr>';
                                echo '<td><input type="checkbox" name="check_' . $line['cislo'] . '" value="' . $line['cislo'] . '" />' . $line['cislo'] . '</td>';
                                $im++;
                            }
                            echo '</tr>';
                        ?>
                        </table>
                        <a onclick="javascript:checkAll('readoutform', true);" href="javascript:void();">check all</a>
                        <br />
                        <a onclick="javascript:checkAll('readoutform', false);" href="javascript:void();">uncheck all</a>                        <br />
			<input type="submit" name="readout-submit" value="Odeslat data"/><br />  
                </span>  
	</form>			
    </body>
</html>
