<?php
class Database
{
  var $db;
  var $lastError;
  
  //connect DB
  function Database($dbName = false, $user = false, $pass = false, $host = false, $port = false)
  {
    if (is_string($dbName))
    {
      $connect = "";
      if (is_string($host)) $connect .= " host=" . $host;
      if (is_string($port)) $connect .= " port=" . $port;
      if (is_string($dbName)) $connect .= " dbname=" . $dbName;
      if (is_string($user)) $connect .= " user=" . $user;
      if (is_string($pass)) $connect .= " password=" . $pass;
      
      if (! $this->db = @pg_connect($connect))
      {
        $this->lastError = DB_ERR_CONNECT . $dbName;
        return false;
      }
    pg_query($this->db, "set datestyle to european");
    }
    else
    {
      $this->db = false;
    }
    return true;
  }

  //list of rows is SQL
  function SQLList($array, $nocommas = false, $separator = ", ")
  {
    if (is_array($array))
    {
   	  //init variable
      $i=0;
      $sql = "";
      foreach($array as $key => $value) 
      {
        if (($value === "null") || ($value === "Null") || ($value === "NULL") || ($value === NULL))
        {
        	$sql .= "null";
        }
        else if ($value === TRUE)
        {
	       	$sql .= "TRUE";
	      }
        else if ($value === FALSE)
        {
	       	$sql .= "FALSE";
	      }
        else if ($nocommas || is_int($value)) $sql .= "$value";         //if nocomas don't add comas
          else $sql .= "\"$value\"";
        //if key set then use AS 
        if (is_string($key)) $sql .= " AS \"$key\""; 
        $i++;
        if ($i < count($array)) $sql .= $separator;
      }
      return $sql;
    }
    else
    {
      if(is_string($array)) return $array;
        else return false;
    }
  }
  
  //list of "Key" = 'Value' in SQL
  function SQLPairList($array, $separator = ", ", $set = false)
  {
  	$sql = "";
    if (is_array($array))
    {
      $i=0;
      foreach($array as $key => $value) 
      {
        if (($value === "null") || ($value === "Null") || ($value === "NULL") || ($value === NULL))
        {
	        if ($set)
	        	$sql .= "\"$key\" = null";
	        else
	        	$sql .= "\"$key\" is null";
        }
        else if ($value === TRUE)
        {
	       	$sql .= "\"$key\" = TRUE";
	      }
        else if ($value === FALSE)
        {
	       	$sql .= "\"$key\" = FALSE";
	      }
        else
        {
        	if (!is_int($value)) $value = "'$value'";
	        $sql .= "\"$key\" = $value";
	    }
        $i++;
        if ($i < count($array)) $sql .= $separator;
      }
      return $sql;
    }
    else
    {
      if(is_string($array)) return $array;
        else return false;
    }
  }
  
  //make SELECT SQL 
  function SQLSelect($select)
  {
    if (is_array($select))
    {
      //SELECT
      $sql = "SELECT ";
      if (isset($select['selectx']) || isset($select['select']))
      {
        if (isset($select['selectx']))
          $sql .= $this->SQLList($select['selectx'], true);
        if (isset($select['selectx']) && isset($select['select']))
          $sql .= ", ";
        if (isset($select['select']))
          $sql .= $this->SQLList($select['select']);
      }
      else $sql .= "* ";
      
      //FROM
      $sql .= " FROM ";
      if (isset($select['table']))
      {
        $sql .= $this->SQLList($select['table']);
      }
      else return false;
      
      //WHERE
      if (isset($select['wherex']) || isset($select['where']))
      {
        $sql .= " WHERE ";
        if (isset($select['wherex']))
          $sql .= $this->SQLList($select['wherex'], true, " AND ");
        if (isset($select['wherex']) && isset($select['where']))
          $sql .= " AND ";
        if (isset($select['where']))
          $sql .= $this->SQLPairList($select['where'], " AND ");
      }
      
      //GROUP BY
      if (isset($select['groupx']) || isset($select['group']))
      {
        $sql .= " GROUP BY ";
        if (isset($select['groupx']))
          $sql .= $this->SQLList($select['groupx'], true);
        if (isset($select['groupx']) && isset($select['group']))
          $sql .= ", ";
        if (isset($select['group']))
          $sql .= $this->SQLList($select['group']);
      }

      //ORDER BY
      if (isset($select['orderx']) || isset($select['order']))
      {
        $sql .= " ORDER BY ";
        if (isset($select['orderx']))
          $sql .= $this->SQLList($select['orderx'], true);
        if (isset($select['orderx']) && isset($select['order']))
          $sql .= ", ";
        if (isset($select['order']))
          $sql .= $this->SQLList($select['order']);
      }
      
      //LIMIT
      if (isset($select['limit']))
      {
        $sql .= " LIMIT " . $select['limit'];
      }
      return $sql;
    }
    else
    {
    if (is_string($select))
      {
        return $select;
      }
      else return false;
    }
  }

  //make DELETE SQL 
  function SQLDelete($delete)
  {
    if (is_array($delete))
    {
      //DELETE FROM
      $sql = "DELETE FROM ";
      if (isset($delete['table']))
      {
        $sql .= $this->SQLList($delete['table']);
      }
      else return false;
      
      //WHERE
      if (isset($delete['wherex']) || isset($delete['where']))
      {
        $sql .= " WHERE ";
        if (isset($delete['wherex']))
          $sql .= $this->SQLList($delete['wherex'], true, " AND ");
        if (isset($delete['wherex']) && isset($delete['where']))
          $sql .= " AND ";
        if (isset($delete['where']))
          $sql .= $this->SQLPairList($delete['where'], " AND ");
      }
      return $sql;
    }
    else
    {
    if (is_string($delete))
      {
        return $delete;
      }
      else return false;
    }
  }

  //make UPDATE SQL 
  function SQLUpdate($update)
  {
    if (is_array($update))
    {
      //UPDATE
      $sql = "UPDATE ";
      if (isset($update['table']))
      {
        $sql .= $this->SQLList($update['table']);
      }
      else return false;
      
      //SET
      if (isset($update['setx']) || isset($update['set']))
      {
        $sql .= " SET ";
        if (isset($update['setx']))
          $sql .= $this->SQLList($update['setx'], true, ", ");
        if (isset($update['setx']) && isset($update['set']))
          $sql .= ", ";
        if (isset($update['set']))
          $sql .= $this->SQLPairList($update['set'], ", ", true);
      }
      
      //WHERE
      if (isset($update['wherex']) || isset($update['where']))
      {
        $sql .= " WHERE ";
        if (isset($update['wherex']))
          $sql .= $this->SQLList($update['wherex'], true, " AND ");
        if (isset($update['wherex']) && isset($update['where']))
          $sql .= " AND ";
        if (isset($update['where']))
          $sql .= $this->SQLPairList($update['where'], " AND ");
      }
      return $sql;
    }
    else
    {
    if (is_string($update))
      {
        return $update;
      }
      else return false;
    }
  }

  //make INSERT SQL 
  function SQLInsert($insert)
  {
    $values = '';
    $keys = '';
    if (is_array($insert))
    {
      //INSERT INTO
      $sql = "INSERT INTO ";
      if (isset($insert['table']))
      {
        $sql .= $this->SQLList($insert['table']);
      }
      else return false;

      if (isset($insert['insertx']) || isset($insert['insert']))
      {      
        $keys .= " (";
        $values .= " VALUES (";
  
        if (isset($insert['insertx']))
        {
          $i=0;
          foreach($insert['insertx'] as $key => $value) 
          {
            $keys .= "\"$key\"";
            $values .= "$value";
            $i++;
            if ($i < count($insert['insertx']))
            {
              $keys .= ", ";
              $values .= ", ";
            }
          }
        }

        if (isset($insert['insertx']) && isset($insert['insert']))
        {
          $keys .= ", ";
          $values .= ", ";
        }
  
        if (isset($insert['insert']))
        {
          $i=0;
          foreach($insert['insert'] as $key => $value) 
          {
            $keys .= "$key";
						if (($value === "null") || ($value === "Null") || ($value === "NULL") || ($value === NULL))
							$values .= "NULL";
			      else if ($value === TRUE)
			       	$values .= "TRUE";
		        else if ($value === FALSE)
			       	$values .= "FALSE";
			      else
						{
							if (is_int($value))
								$values .= "$value";
							else
								$values .= "'$value'";
						}
            
            $i++;
            if ($i < count($insert['insert']))
            {
              $keys .= ", ";
              $values .= ", ";
            }
          }
        }
        $keys .= ")";
        $values .= ")";
        unset($i);
        return $sql . $keys . $values . '; ';
      }
      else return false;
    }
    else
    {
    if (is_string($insert))
      {
        return $insert;
      }
      else return false;
    }
  }
    
  function dbSelect ($select, $index = false, $indexnotindata = false)
  {
  	$output = false;
  	$sql = $this->SQLSelect($select);
  	
  	if ($result = pg_query($this->db, "$sql"))
  	{
  		while($line = pg_fetch_assoc($result))
  		{
  		  if (!$index) $output[] = $line;
          else
					{
						$output[$line[$index]] = $line;
						if ($indexnotindata) unset($output[$line[$index]][$index]);
					}
  		}
  		if (! $output) $output = array();
  	}
  	else
  	{
  		$this->lastError = DB_ERR_CR;
  		$this->lastError .= " (" . pg_last_error($this->db) . ")";
   		$output = false;
  	}  
  	return $output;
  }

  function dbDelete ($delete)
  {
  	$output = false;
  	$sql = $this->SQLDelete($delete);
  	
  	if ($result = pg_query($this->db, "$sql"))
  	{
  		$output = pg_affected_rows($result);
  	}
  	else
  	{
  		$this->lastError = DB_ERR_CU;
  		$this->lastError .= " (" . pg_last_error($this->db) . ")";
   		$output = false;
  	}  
  	return $output;

  }

  function dbUpdate ($update)
  {
  	$output = false;
  	$sql = $this->SQLUpdate($update);
  	
  	if ($result = pg_query($this->db, "$sql"))
  	{
  		$output = pg_affected_rows($result);
  	}
  	else
  	{
  		$this->lastError = DB_ERR_CU;
  		$this->lastError .= " (" . pg_last_error($this->db) . ")";
   		$output = false;
  	}  
  	return $output;
  }
  
  function dbInsert ($insert, $index = false)
  {
  	$output = false;
  	$sql = $this->SQLInsert($insert);
  	
  	if ($result = pg_query($this->db, "$sql"))
  	{
  		$output = pg_affected_rows($result);
  	}
  	else
  	{
  		$this->lastError = DB_ERR_CU;
  		$this->lastError .= " (" . pg_last_error($this->db) . ")";
   		$output = false;
  	}  
  	return $output;
  }
}
?>