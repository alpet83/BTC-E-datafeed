<pre><?php

  $date = new DateTime ('now', new DateTimeZone('UTC'));

  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');                                                                 
  include_once('lib/config.php');
  include_once('lib/db_tools.php');

  log_msg("script started...");
  set_time_limit(30);
    
  $date_dir = "/var/www/btc-e/trades/".$date->format('Ymd');

  $ts = $date->format('Y-m-d H:i:s');
  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";

  $trades_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $trades_fields['ts'] = 'timestamp NOT NULL';
  $trades_fields['price']    = $double_field;
  $trades_fields['order_id'] = 'bigint(20) NOT NULL default 0';
  $trades_fields['trade_id'] = 'bigint(20) NOT NULL';
  $trades_fields['flags']    = 'int(11) unsigned NOT NULL';
  $trades_fields['volume']   = $double_field;

  function sync_for_pair($pair)
  {
     global $trades_fields, $last_url, $db_alt_server, $db_user, $db_pass;
     $old_id = 0;
     
     $need_full = false;
     $last_ts = false;

     $local_cnt = 0;

     // оценка количества
     $result = select_from('COUNT(trade_id)', $pair, '');         
     if ($result && $row = mysql_fetch_array($result, MYSQL_NUM))
         $local_cnt = $row[0];
         
     $last_id = select_value('trade_id', $pair, 'ORDER by trade_id DESC');    
         
     if (0 == $local_cnt || 0 == $last_id) return;
     
     log_msg("in local db.$pair  have $local_cnt ticks, last trade id = $last_id");

     $remote = new mysqli($db_alt_server, $db_user, $db_pass);
     
     if ($remote && 0 == mysqli_connect_errno())    
        log_msg("connectted to $db_alt_server  ...");
     else
     {
        log_msg(" failed connect to remote server $db_alt_server");
        return;
      }
      
     $remote->select_db('trades_history'); 

     $query  = "SELECT COUNT(trade_id) FROM trades_history.$pair WHERE trade_id <= $last_id;";  
     $result = $remote->query($query);        
     $remote_cnt = -1;
     if ($result)
     {
        // printf(" query result num_rows = %d \n", $result->num_rows);
        $row = $result->fetch_array(MYSQL_NUM);        
        $remote_cnt = $row[0] or 0;
      }
     else
         log_msg("failed [$query] with error: ".$remote->error);         
         
     log_msg("in remote db.$pair  have $remote_cnt ticks");    
      
     if ($local_cnt < $remote_cnt)
     {
        $fields = 'trade_id,order_id,QUOTE(ts),price,flags,volume';
        $local_rows = select_from($fields, $pair, "ORDER BY trade_id");
        
        $query = "SELECT $fields FROM $pair ORDER BY trade_id;";
        $remote_rows = $remote->query($query);
        
        $old_data = array();
        while ($row = mysql_fetch_array($local_rows, MYSQL_NUM))
        {
          $id = $row[0];
          $old_data[$id] = $row;          
        }       
                
        $add_data = array();
        while ($row = $remote_rows->fetch_array(MYSQL_NUM))
        {
          $id = $row[0];
          if (!isset($old_data[$id]))
          {             
             $line = '('.implode($row, ',').')';
             $add_data []= $line;
          }                
        }
        
        $cnt = count($data);
        
        $fields = 'trade_id,order_id,ts,price,flags,volume';
        
        if ($cnt > 0)        {        
            log_msg("adding $cnt new ticks");
                            
            $query = "INSERT INTO $pair($fields)\nVALUES\n";        
            $query .= implode($add_data, ",\n");
            // log_msg("$query");        
            try_query($query);
            $table =  $pair.'__bars';
            try_query("TRUNCATE TABLE $table");
             // recreate table
            $fields = 'trade_id,order_id,ts,price,flags,volume';
            $table_new = "`$pair.new`";
            make_table_ex($table_new, $trades_fields, 'trade_id', ", KEY `SCAN` (`id`, `ts`, `order_id`)");
            try_query("TRUNCATE TABLE $table_new");
            $query = "INSERT INTO $table_new ($fields)\n";
            $query .= "SELECT $fields FROM $pair\n";
            $query .= "ORDER BY trade_id";       
            try_query($query);
            try_query("DROP TABLE $pair");     
            $query = "RENAME TABLE $table_new TO $pair;\n";
            try_query($query);            
        }
        // 
     } 



     $remote->close();
     $remote = null;    
  }

  log_msg("connecting to local host DB");

  $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.mysql_error());
  mysql_select_db("trades_history") or die("cannot select DB trades_history\n");

  foreach ($save_pairs as $pair)
  {
     sync_for_pair($pair);     
  }  

  mysql_close($link);
?>
</pre>