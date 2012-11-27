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
              if (isset($_POST['input']) && ($_POST['input'] == "hill-climb"))
              {
                  echo 'Loading... ';
                  
                  $number = (int)$_POST['number'];
                  
                  $person = 'a';
                  if (!(stristr($_POST['number'], 'a') === FALSE)) $person = 'a';
                  if (!(stristr($_POST['number'], 'b') === FALSE)) $person = 'b';
                  //echo $person;
                  
                  $distance = $_POST['distance'];
                  
                  $result = $database->query("SELECT * FROM vyjezdy WHERE start_cislo = $number;");
                  if (($count = $database->affected_rows) > 1) echo 'PROBLEM - MULTIPLE RECORDS !!!';
                  //echo $count;
                  $line = $result->fetch_assoc();
                  //var_dump($line);
                  echo $database->error;
                  
                  if ($count == 0)
                  {
                      echo 'První pokus... ';
                      echo 'Inserting... ';
                      $database->query("INSERT INTO vyjezdy (start_cislo, {$person}1) VALUES ($number, $distance);");
                      echo $database->error;
                  }
                  else if ($count = 1)
                  {
                      if (is_null($line[$person . '1']))
                      {
                          echo 'První pokus... ';
                          echo 'Updating... ';
                          $result = $database->query("UPDATE vyjezdy SET {$person}1 = $distance WHERE start_cislo = $number;");
                          echo $database->error;
                      }
                      else if (is_null($line[$person . '2']))
                      {
                          echo 'Druhý pokus... ';
                          echo 'Updating... ';
                          $result = $database->query("UPDATE vyjezdy SET {$person}2 = $distance WHERE start_cislo = $number;");
                          echo $database->error;
                      }
                      else echo 'TŘETÍ POKUS, NEZAPSÁNO !!!';
                  }
                  echo "<br />Done";
                  $result = $database->query("SELECT * FROM vyjezdy WHERE start_cislo = $number;");
                  $line = $result->fetch_object();
                  var_dump($line);
                  echo $database->error;
              }
        ?>
	<form action="" method="post" enctype="multipart/form-data">  
		<span> 
			<strong>Přidat nový výjezd</strong><br /> 
			<input type="hidden" name="input" value="hill-climb"/> 
			Start č. (xxx/xxxA/xxxB) <input type="text" name="number" value=""/>  
			Metry<input type="text" name="distance" value=""/><br />  
			<input type="submit" name="import-submit" value="Odeslat data"/><br />  
		</span>  
	</form>			
    </body>
</html>
