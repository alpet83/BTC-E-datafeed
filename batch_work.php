<?php
  header("Access-Control-Allow-Origin:*");
    
  include_once('lib/btc-e.api.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  include_once('lib/common.php');
  
  ob_implicit_flush();
  
  $date = utc_time();
  $debug = true;
  set_time_limit(30);
  
  if ($argc < 5)  
    die(" neeed 4+ arguments!\n sample: batch_work.php depth_history all DTS %s__stats \n");
    
  $db     = $argv[1];
  $pairs  = $argv[2];  
  $action = $argv[3];
  $table  = $argv[4]; 
  
   
  function process($pair)  
  {
     global $mysqli, $action, $table;
     echo " processing [$pair]\n";
     $tname = sprintf($table, $pair);   
     $res = "Unknown action [$action]";
     
     if ('DTS' == $action )
         $res = $mysqli->try_query("ALTER TABLE $tname DISCARD TABLESPACE");  

     if ('ITS' == $action )
         $res = $mysqli->try_query("ALTER TABLE $tname IMPORT TABLESPACE");
         
     if ($res)   
          echo "query result: ".print_r($res, true)."\n";     
  }

  echo "batch_work, db = [$db], pair(s) = [$pairs], action = [$action], table = [$table] \n";
   
  if (strlen($pairs) >= 7)
  {
     init_db($db);
     process($pairs, true);       
  }
  else
  if ($pairs == 'all')  
  {
     init_db($db);
     foreach ($save_pairs as $pair)
     {
        process($pair);        
     } // foreach
  }  

  if ($mysqli) $mysqli->close();
  echo " script complete for pair [$pair] \n";
?>
