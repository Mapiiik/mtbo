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
            
            $dbf = new Database();
            ////////////////
            // PROCESSING //
            ////////////////
              if (isset($_POST['import-csv']) && ($_POST['import-csv'] == "upload"))
              {
                  echo 'Loading ...';
                  
                  $file = iconv('CP1250', 'UTF-8', file_get_contents($_FILES['uploaded_file']['tmp_name']));
                  foreach (explode("\r\n", $file) as $line)
                  {
                      $data = explode(";", $line);
                      if (!isset($index))
                      {
                          foreach ($data as $key => $value) $index[$value] = $key;
                          $database->query('TRUNCATE TABLE ucastnici');
                      }
                      else if (count($data) > 1)
                      {
                          $insert['insert']['start_cislo'] = $data[$index['startcislo']];
                          $reg = date_parse_from_format("j.n.Y H:i:s", $data[$index['datum']] . ' ' . $data[$index['cas']]);
                          $insert['insert']['registrace'] = $reg['year'] . '-' . $reg['month'] . '-' . $reg['day'] . ' ' . $reg['hour'] . ':' . $reg['minute'] . ':' . $reg['second'];
                          $insert['insert']['kategorie'] = $data[$index['kat']];
                          $insert['insert']['a_prijmeni'] = $data[$index['a_prijmeni']];
                          $insert['insert']['a_jmeno'] = $data[$index['a_jmeno']];
                          $insert['insert']['a_datum_narozeni'] = $data[$index['a_rok']] . '-' . $data[$index['a_mesic']] . '-' . $data[$index['a_den']];
                          if ($data[$index['b_prijmeni']]) $insert['insert']['b_prijmeni'] = $data[$index['b_prijmeni']];
                          if ($data[$index['b_jmeno']]) $insert['insert']['b_jmeno'] = $data[$index['b_jmeno']];
                          if ($data[$index['b_rok']]) $insert['insert']['b_datum_narozeni'] = $data[$index['b_rok']] . '-' . $data[$index['b_mesic']] . '-' . $data[$index['b_den']];
                          $insert['insert']['si_cip'] = $data[$index['SI']];
                          $insert['insert']['tym'] = addslashes($data[$index['tym']]);
                          $insert['insert']['email'] = $data[$index['email']];
                          $insert['insert']['telefon'] = $data[$index['telefon']];
                          $insert['insert']['zaplatit'] = $data[$index['zaplatit']];
                          $insert['insert']['zaplaceno'] = $data[$index['zaplaceno']];
                          $insert['insert']['poznamka'] = $data[$index['pozn']];
                          if (is_numeric($data[$index['SI CIP zavodni']]) && ($data[$index['SI CIP zavodni']] > 0)) $insert['insert']['si_cip_zavodni'] = $data[$index['SI CIP zavodni']];
                          $insert['insert']['start_cas'] = $_POST['date'] . ' ' . $data[$index['startcas']];
                          $insert['table'] = 'ucastnici';
                          
                          //echo $dbf->SQLInsert($insert);
                          $database->query($dbf->SQLInsert($insert));
                          unset($insert);
                          echo $database->error;
                      }
                  }
                  echo "OK";
                  //remove file
                  unlink($_FILES['uploaded_file']['tmp_name']);
              }
        ?>
	<form action="" method="post" enctype="multipart/form-data">  
		<span> 
			<strong>Načíst nová data</strong><br /> 
			<input type="hidden" name="import-csv" value="upload"/> 
			<input type="file" name="uploaded_file" accept="application/dbase "/><br />  
			Datum (YYYY-MM-DD):<input type="text" name="date" value=""/><br />  
			<input type="submit" name="import-submit" value="Odeslat data"/><br />  
		</span>  
	</form>			
    </body>
</html>
