<?php

  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');

  set_time_limit(1500);

  function transfer_one($pair)
  {   
    $size_start = select_value('COUNT(id)', $pair.'__full');
    log_msg(" [$pair] grouping data, inital size = $size_start");
    
    try_query( sprintf('TRUNCATE TABLE %s__temp', $pair) );
    $query = 'INSERT INTO %1$s__temp(ts,price,volume,flags) '; 
    // $query .= 'SELECT ts,price,volume,flags FROM %1$s__full GROUP BY ts, price ORDER BY ts;'."\n";
    $query .= 'SELECT ts,price,volume,flags FROM %1$s__full WHERE MINUTE(ts) < 10 ORDER BY ts;'."\n";
    $query = sprintf($query, $pair);
    
    log_msg($query);
    if (!try_query($query)) return false;
    
    $size_new = select_value('COUNT(id)', $pair.'__temp');
    if (!$size_new) return false; 
    
    log_msg(" grouped data size = $size_new"); 
    
    if ($size_new + 200 < $size_start)
    {
      log_msg(" optimization approved! ");
      
      // $query = sprintf('RENAME TABLE %1$s__full TO %1$s__old;', $pair);      
      try_query("DROP TABLE $pair".'__full');                                
      
      $query = sprintf('RENAME TABLE %1$s__temp TO %1$s__full;', $pair);
      log_msg($query);  
      if (strlen($query) > 10) try_query( $query ); 
    }
    else
      try_query("DROP TABLE $pair".'__temp');
        
    
    
  }

  $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.mysql_error());
  mysql_select_db("depth_history") or die('cannot select DB depth_history');
  
  for ($i = 3; $i < count($save_pairs); $i++)
       transfer_one($save_pairs[$i]);
  
  mysql_close($link);  
?>