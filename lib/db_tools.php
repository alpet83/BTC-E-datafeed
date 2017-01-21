<?php
  include_once('common.php');
  $table_params = "";

  // ", KEY `TIMESTAMP` (`ts`)"
  $link   = false;
  $mysqli = false;
  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";
  
    
  // OOP wrapper
  class mysqli_ex extends mysqli 
  {
     public $extended = true;  
  
     function try_query($query, $rmode = MYSQLI_STORE_RESULT)
     {
        $result = $this->query($query);
        if (!$result)
        {            
          $err = $this->error;
          log_msg("#FAILED [$query] with error:\n\t$err\n");
         print_traceback();           
        }
        return $result;
     } // try_query  
     
     function select_from($fields, $table, $params, $rmode = MYSQLI_STORE_RESULT)
     {       
        return $this->try_query("SELECT $fields FROM $table\n$params", $rmode);        
     }

     function select_row($fields, $table, $params, $type = MYSQL_NUM)
     {
        $r = $this->select_from($fields, $table, "$params\n LIMIT 1", $type);     
        if (!$r) return null;           
        return $r->fetch_array($type);  
     } 
      
      function select_value($field, $table, $params)
      {
        $row = $this->select_row($field, $table, $params);
        if ($row)
            return $row[0];
        else
            return null;
      }
      
      function table_exists($table)
      {
        $r = $this->try_query("SHOW TABLES LIKE '$table'");
        return ($r && $r->num_rows == 1);
      }
      
  }


  function init_db($db_name = false, $new = true)
  { 
    global $link, $mysqli, $db_user, $db_pass;
     
    if ($new)
    {
      $link = new mysqli_ex('localhost', $db_user, $db_pass); 
      $mysqli = $link;
      
      if ($link->connect_error) 
          die('cannot connect to DB server: '.$link->connect_error);  
          
      if ($db_name) $link->select_db($db_name); // or die('cannot select DB depth_history');
    }
    else
    {
      $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.$link->error);
      if ($link && $db_name)
          mysql_select_db($db_name, $link);
      $mysqli = false;
    }
  }

  function table_exists($table)
  {
    return (mysql_num_rows(mysql_query("SHOW TABLES LIKE '$table'")) == 1);
  }

  function try_query($query, $link = null) // obsolete procedural variant(!)
  {
     global $mysqli;
     $result = false;
     
     if ($mysqli)
       $result = $mysqli->query($query);
     else     
     {
       if ($link)
           $result = mysql_query($query, $link);
       else
           $result = mysql_query($query);
     }
       
     if (!$result)
     {            
       $err = '?';
       if ($mysqli)
           $err = $mysqli->error;
       else
           $err = mysql_error();
       log_msg("#FAILED [$query] with error:\n\t$err\n");
       print_traceback();
       
     }
     return $result;
  }
  

  function make_index($table, $name, $col)
  {
     $query = "CREATE INDEX $name ON $table($col)";
     try_query($query);
  }

  function make_table_ex ($table, $fields, $pk, $params)
  {
     $query = "CREATE TABLE IF NOT EXISTS $table (\n";
     $keys = array_keys($fields);
     foreach ($keys as $k)
     {
        $t = $fields[$k];
        $query .= "`$k` $t,\n";
     }
     $query .= "PRIMARY KEY(`$pk`)";
     if ($params) $query .= $params;

     //$query .= "ON DELETE NO ACTION\n";
     //$query .= "ON UPDATE NO ACTION)\n";
     $query .= ")\n ENGINE = InnoDB\n";
     $query .= "DEFAULT CHARACTER SET = utf8\n";
     $query .= "COLLATE = utf8_unicode_ci\n";


     // $query .= "DATA DIRECTORY = '/var/tmp/mysql'\n";
     return try_query($query) or die("make_table failed [\n".$query.'] with errors:\n '.mysql_error());
  }

  function make_table ($table, $fields, $params)
  {
     return make_table_ex($table, $fields, 'id', $params);
  }

  function select_from($fields, $table, $params, $type = MYSQL_NUM)
  {    
     $query = "SELECT $fields FROM $table\n$params";     
     $r = try_query($query);
     return $r;
  }

  function select_row($fields, $table, $params, $type = MYSQL_NUM)
  {
     $r = select_from($fields, $table, "$params\n LIMIT 1", $type);     
     if (!$r)             
        return null;           
     return mysql_fetch_array($r, $type);  
  } 
  
  function select_value($field, $table, $params)
  {
    $row = select_row($field, $table, $params);
    if ($row)
        return $row[0];
    else
        return null;
  }

  function pair_id($pair)
  {
    $query = "SELECT id FROM pair_map\n";
    $query .= "WHERE pair = '$pair';\n";
    // echo ("pair_id query:\n $query ");
    $result = mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());
    $row = false;
    if ($result)
        $row = mysql_fetch_array ( $result, MYSQL_NUM );

    if ( !$row || count($row) == 0 )
    {
       $add = "INSERT INTO pair_map (pair) VALUES('$pair');";
       log_msg("add_rqs: $add ");
       $result = mysql_query($add);
       if ($result) mysql_free_result($result);
       $result = mysql_query($query);
       $row = mysql_fetch_array ( $result, MYSQL_NUM );
    }


    $id = -1;

    if ( count($row) > 0 ) $id = $row [0];
    if ($result) mysql_free_result($result);
    return $id;
  }

?>