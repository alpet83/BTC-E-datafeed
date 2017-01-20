<?php
  function make_table($table, $fields)
  {
     $query = "CREATE TABLE IF NOT EXISTS `$table` (\n";
     $keys = array_keys($fields);
     foreach ($keys as $k)
     {
        $t = $fields[$k];
        $query .= "`$k` $t,\n";  
     }   
     $query .= "PRIMARY KEY(`id`))\n"; 
     //$query .= "ON DELETE NO ACTION\n";
     //$query .= "ON UPDATE NO ACTION)\n";
     $query .= "ENGINE = InnoDB\n";
     $query .= "DEFAULT CHARACTER SET = utf8\n";
     $query .= "COLLATE = utf8_bin\n";
     mysql_query($query) or die("make_table: Failed ' $query ' with errors:\n".mysql_error());	

  }

?>
