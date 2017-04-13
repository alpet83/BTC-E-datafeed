<?php

  include_once('common.php');

  $table_params = "";
  $color_errors = true;
  $ustr = '_';

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
        global $color_errors;
        $result = $this->query($query);
        $ct_open = '';
        $ct_close = '';
        if ($color_errors)
        {
          $ct_open = '<font color=red><b>';
          $ct_close = '</b></font>';        
        }
        
        if (!$result)
        {            
          $err = $this->error;        
          log_msg("$ct_open#FAILED$ct_close [$query] with error:\n\t$err\n");
          print_traceback();           
        }
        return $result;
     } // try_query  
     
     function select_from($fields, $table, $params = '', $rmode = MYSQLI_STORE_RESULT)
     {       
        return $this->try_query("SELECT $fields FROM $table\n$params", $rmode);        
     }

     function select_row($fields, $table, $params = '', $type = MYSQL_NUM)
     {
        $r = $this->select_from($fields, $table, "$params\n LIMIT 1", $type);     
        if (!$r) return null;           
        return $r->fetch_array($type);  
     } 
      
      function select_value($field, $table, $params = '')
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
      { 
          echo('cannot connect to DB server: '.$link->connect_error);
          $link = false;
          $mysqli = false;
      }  
      else    
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


  function mysql_err()
  {
     global $mysqli;
     if ($mysqli)
         return $mysqli->error;
     else
         return mysql_error();
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
       $err = mysql_err();       
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

  function make_table_ex ($table, $fields, $pk, $params = '')
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
     return try_query($query) or 
            die("make_table failed [\n".$query.'] with errors:\n '.mysql_err());
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
    global $mysqli;
    
    $row = $mysqli->select_row('id', 'pair_map', "WHERE pair = '$pair'");

    if ( !$row || count($row) == 0 )
    {
       $add = "INSERT INTO pair_map (pair) VALUES('$pair');";
       log_msg("add_rqs: $add ");
       $mysqli->try_query($add);              
       $row = $mysqli->select_row('id', 'pair_map', "WHERE pair = '$pair'");       
    }

    $id = -1;

    if ( count($row) > 0 ) $id = $row [0];    
    return $id;
  }

  function on_data_update($dtype, $ts)
  {
     global $mysqli;
        
     
     
     $query = "INSERT INTO datafeed.last_sync (data_type, ts)\n";
     $query .= " VALUES ('$dtype', '$ts')\n";
     $query .= " ON DUPLICATE KEY UPDATE\n ts=VALUES(ts)";
     if (!$mysqli)    
         log_msg("#WARN: mysqli object == null");
     
     try_query($query);
  }
?>
