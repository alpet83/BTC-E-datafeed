<pre>
<?php
  set_time_limit(30);
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');



  $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.mysql_error());
  mysql_select_db('trades_history') or die('cannot select DB');  
  
  $pair = rqs_param('pair', 'btc_usd');  
  // $id = select_value('id', 'pair_map', "WHERE pair = '$pair'");  
  // echo " pair_id for $pair = $id \n";
  $fields = 'ts, price, order_id, trade_id, flags, volume';
  
  echo "$fields\n";
  
  $first_id = rqs_param('trade_id', 0);
    
  $query = "SELECT $fields FROM trades_history.$pair \n";
  $query .= "WHERE trade_id >= $first_id\n";
  $query .= "LIMIT 5000\n"; 
    
  $r = mysql_query($query) or die("failed execute [$query] with error: ".mysql_error());
   
  $lines = array(); 
   
  while ($row = mysql_fetch_array($r, MYSQL_NUM))
  {
     $row[0] = "'".$row[0]."'";
     $l = implode($row, ',');       
     $lines[]= $l;                
     if (count($lines) > 1000)
     {
        echo implode($lines, "\n");
        $lines = array();        
     } 
  
  } 
  
  echo implode($lines, "\n");   
  
  
  mysql_close($link);
?>