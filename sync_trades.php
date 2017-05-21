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

  $db_alt_server = rqs_param('server', $db_alt_server);
     

  function sync_for_pair($pair)
  {
     global $argv, $mysqli, $trades_fields, $last_url, $db_alt_server, $db_user, $db_pass;
     $old_id = 0;
     
     $need_full = false;
     $last_ts = false;

     $local_cnt = 0;
     log_msg("------------------------------------ PROCESSING $pair ----------------------------------");
     // оценка количества
     
     $start_ts = '2017-05-01 00:00:00';
         
     if ( isset($argv[2]) )
     {       
       $s = $argv[2];
       echo " arg2: [$s] ";
       
       if (preg_match('/\d+-\d+-\d+/', $s) == 1)
       {
           $start_ts = "$s 00:00:00";
           echo " - ts assigned [$start_ts] ";          
       }    
       
       echo "\n";
     }
          
     $result = $mysqli->select_from('COUNT(trade_id)', $pair, "WHERE ts > '$start_ts'");         
     if ($result && $row = $result->fetch_array(MYSQLI_NUM))
         $local_cnt = $row[0];
         
     $last_id = $mysqli->select_value('trade_id', $pair, 'ORDER by trade_id DESC');    
         
     if (0 == $local_cnt || 0 == $last_id) return;
     
     log_msg("in local db.$pair  have $local_cnt ticks, last trade id = $last_id");


     $remote = init_remote_db($db_user, $db_pass); // select one from accessible server
     
     if ($remote && 0 == mysqli_connect_errno())    
       echo " remote DB connected at $db_alt_server \n";
     else
     {
        log_msg(" failed connect to remote server $db_alt_server");
        return;
      }
      
      
     
     
     //*/
     
      
     $remote->select_db('trades_history'); 

     $query  = "SELECT COUNT(trade_id) FROM trades_history.$pair WHERE (ts > '$start_ts') AND (trade_id <= $last_id); ";  
     $result = $remote->query($query);        
     $remote_cnt = -1;
     if ($result)
     {
        // printf(" query result num_rows = %d \n", $result->num_rows);
        $row = $result->fetch_array(MYSQLI_NUM);        
        $remote_cnt = $row[0] or 0;
      }
     else
         log_msg("failed [$query] with error: ".$remote->error);         
         
     log_msg("in remote db.$pair  have $remote_cnt ticks after [$start_ts] before #$last_id");    
      
     if (1) // $local_cnt < $remote_cnt
     {
      
        $fields = 'trade_id,order_id,QUOTE(ts),price,flags,volume';
        $local_rows   = $mysqli->select_from($fields, $pair, "WHERE ts > '$start_ts'  ORDER BY trade_id");
        
        // $query = "SELECT $fields FROM $pair ;                
        $remote_rows = $remote->select_from($fields, $pair, "WHERE ts > '$start_ts' ORDER BY trade_id");        
        $old_data = array();
        $cnt_local = 0;
        while ($row = $local_rows->fetch_array(MYSQLI_NUM))
        {
          $id = $row[0];
          $old_data[$id] = $row;
          $cnt_local ++;          
        }       
         
        log_msg("loaded $cnt_local ticks from local DB"); 
           
                
        $add_data = array();        
        $checked = 0;
        
        
        while ($row = $remote_rows->fetch_array(MYSQLI_NUM))
        {
          $id = $row[0];
          $checked ++;
          if (!isset($old_data[$id]))
          {             
             $line = '('.implode($row, ',').')';
             $add_data []= $line;
          }                
        }
        
        $cnt = count($add_data);
        
        $fields = 'trade_id,order_id,ts,price,flags,volume';
        
        if ($cnt > 0) {        
            log_msg("adding $cnt new ticks");
            // добавление исключительно отсутствующих данных, в конец таблицы (unsorted)
            $acnt = 0;
            set_time_limit(200);
            $query = "INSERT IGNORE INTO $pair($fields)\nVALUES\n";
            batch_query($query, '', $add_data);
            /*                       
            while (count($add_data) > 0) {
               $query = "INSERT IGNORE INTO $pair($fields)\nVALUES\n";                        
               
               $slice = array();                             
                              
               if (count($add_data) <= 5000)
               {
                   $slice = $add_data;
                   $add_data = array();
               }    
               else   
                   $slice = array_splice($add_data, 0, 5000);                                         
               
               $query .= implode($slice, ",\n");
               
               
                   
               // log_msg("$query");      
               try_query($query);
               $add = count($slice);
               $acnt += $add;
               $cnt = count($add_data);
               log_msg(" inserted  $add, total inserted $acnt ticks, rest $cnt ");
            }
            //*/
            
            $table =  $pair.'__bars';
            try_query("TRUNCATE TABLE $table"); // clearing bars
             // recreate table
            $fields = 'trade_id,order_id,ts,price,flags,volume';
            $table_new = "`$pair.new`";
            log_msg("creating temporary table $table_new");
            
            make_table_ex($table_new, $trades_fields, 'trade_id', ", KEY `SCAN` (`id`, `ts`, `order_id`)");
            
            // transfer data with reorder fields
            try_query("TRUNCATE TABLE $table_new");
            $query = "INSERT INTO $table_new ($fields)\n";
            $query .= "SELECT $fields FROM $pair\n";
            $query .= "ORDER BY trade_id";       
            try_query($query);
            try_query("DROP TABLE $pair");     
            $query = "RENAME TABLE $table_new TO $pair;\n";
            $rr = try_query($query);
            print_r($rr);                        
        }
        else
            log_msg(" checked $checked ticks, all present in local DB.");
        //  
     } 



     $remote->close();
     $remote = null;    
  }

  log_msg("connecting to local host DB");

   
  init_db("trades_history");
  
  $pair = rqs_param('pair', '');
  
  print_r ($argv); 

  if ('' == $pair && isset($argv[1]))
      $pair = $argv[1];    
  
  
  if ($pair != 'all')
  {
     sync_for_pair($pair);
  }     
  else
    foreach ($save_pairs as $pair)
    {
       sync_for_pair($pair);     
    }  

  if ($mysqli) $mysqli->close();
  echo " script complete for pair [$pair] \n";  
?>
</pre>