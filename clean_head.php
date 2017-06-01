
<?php
  header("Access-Control-Allow-Origin:*");
    
  include_once('lib/btc-e.api.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  include_once('lib/common.php');
  
  ob_implicit_flush();
  
  $date = utc_time();

  $mstart = $date->format('Y-m-01') . ' 00:00:00';
  set_time_limit(30);
  init_db('depth_history');
  
  
  function process($pair)
  {
     global $mstart, $mysqli;
     set_time_limit(30);
     
     log_msg("processing [$pair]");     
     $query = "DELETE FROM $pair\137_full WHERE ts < '$mstart'; ";
     echo " trying: $query \n";
     if ($mysqli->try_query($query))
         log_msg(" affected rows: ".$mysqli->affected_rows);
     
     $query = "DELETE FROM $pair\137_diff WHERE ts < '$mstart'; ";
     echo " trying: $query \n";
     if ($mysqli->try_query($query))
         log_msg(" affected rows: ".$mysqli->affected_rows);     
  }
  
  
  foreach ($save_pairs as $pair)
  {
    process($pair);        
  } 
  


?>