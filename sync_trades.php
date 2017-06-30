<pre><?php

  $date = new DateTime ('now', new DateTimeZone('UTC'));

  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');                                                                 
  include_once('lib/config.php');
  include_once('lib/db_tools.php');

  log_msg("script started...");
  set_time_limit(30);
  ob_implicit_flush();
  
    
  $date_dir = "/var/www/btc-e/trades/".$date->format('Ymd');

  $ts = $date->format('Y-m-d H:i:s');
  $remote_total = 0;
  
  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";

  $trades_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $trades_fields['ts'] = 'timestamp NOT NULL';
  $trades_fields['price']    = $double_field;
  $trades_fields['order_id'] = 'bigint(20) NOT NULL default 0';
  $trades_fields['trade_id'] = 'bigint(20) NOT NULL';
  $trades_fields['flags']    = 'int(11) unsigned NOT NULL';
  $trades_fields['volume']   = $double_field;

  $alt_server = rqs_param('server', $db_alt_server);
  
  echo "checking [$alt_server] registered in db_servers list... ";
  if (array_search($alt_server, $db_servers) !== false)
    {        
      $db_alt_server = $alt_server;
      echo "server parameter [$alt_server] accepted !\n";
    }  
  echo "\n";  
  ob_end_flush();

  function add_ts_index($link, $pair)
  {
    $tmp = $link->try_query("SHOW CREATE TABLE `$pair`");
    $row = $tmp->fetch_array(MYSQLI_NUM);        
    // print_r($row);
    if (strpos($row[1], 'KEY `TS`') === false)
    {                
      $query = "ALTER TABLE `trades_history`.`$pair` ADD INDEX `TS` (`ts`) USING BTREE";
      
      $link->try_query($query);
    }  
  
  }
  
  function find_id($link, $table, $ts, $back = false)
  {
    $row = array();
    
    if ($back) // сканирование с конца таблицы в начало 
        $row = $link->select_row('ts, ref_id', $table, "WHERE (ts <= '$ts') ORDER BY ref_id DESC");
    else    
        $row = $link->select_row('ts, ref_id', $table, "WHERE (ts >= '$ts') ORDER BY ref_id ASC");
        
    if ($row && isset($row[1]))
        return $row[1];       
  }
  

  function sync_for_pair($pair, $month)
  {
     global $ts, $argv, $mysqli, $remote, $trades_fields, $last_url, $db_alt_server, $db_user, $db_pass, $first_ts, $remote_total;
     
     $old_id = 0;
     
     $need_full = false;
     $last_ts = false;

     $local_cnt = 0;
     // оценка количества
     
     $start_ts = '2011-01-01 00:00:00';
     if (strpos($pair, 'nvc') !== false)
         $start_ts = '2013-01-01 00:00:00';
     
     if (!$first_ts)     
          $first_ts = $remote->select_value('MIN(ts)', $pair, '');

     $tsmap = "$pair\137_tsmap";
       
          
     $date = utc_time($start_ts);    
     
     if ($month > 0)         
     {
        $ofs = new DateInterval("P$month".'M');
        $date->add($ofs);
     }
     else
     {   
       log_msg("------------------------------------ PROCESSING $pair ----------------------------------");
       set_time_limit(900); 
       log_msg(" creating local index...");         
       add_ts_index($mysqli, $pair);         
       log_msg(" creating remote index...");
       add_ts_index($remote, $pair);       
            
       // 
       $query = "CREATE TABLE IF NOT EXISTS `$tsmap`\n";       
       $query .= "( `ts` timestamp NOT NULL,\n";
       $query .= "  `ref_id` int(11) unsigned NOT NULL,\n";
       $query .= " PRIMARY KEY (`ref_id`),\n";
       $query .= " KEY `TIMESTAMP` (`ts`)\n";
       $query .= ")\n ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;";       
       $remote->try_query($query);
       // $remote->try_query("TRUNCATE TABLE $tsmap");
        
       $last_ts = $remote->select_value('MAX(ts)', $tsmap);       
       
       $query = "SELECT MIN(ts), MIN(trade_id) FROM $pair\n";
       if ($last_ts)
       {
          echo " last $tsmap.ts = [$last_ts] \n";
          $query .= "WHERE ts >= '$last_ts'";
       }
       $query .= "GROUP BY CONCAT (YEAR(ts), '-', MONTH(ts))";       
       $rows = $remote->try_query($query);
       log_msg(" loaded tsmap for remote DB ");
              
       $lines = array();
       
       while ($row = $rows->fetch_array(MYSQLI_NUM))
       {
          $dt = utc_time($row[0]);
          $rid = $row[1];
          $dts = $dt->format('Y-m-01 00:00:00'); // month start        
          $lines []= "('$dts', $rid)";     
       }
       $query = "INSERT IGNORE INTO $tsmap (ts, ref_id)\n VALUES \n";
       $query .= implode(",\n", $lines);
       $remote->try_query($query);
       
     }     

         
     $start_ts = $date->format('Y-m-d 00:00:00');
     $ofs = new DateInterval('P1M');          
     $end_ts = $date->add($ofs)->format('Y-m-d 00:00:00');     

     if ($end_ts < $first_ts)
     {
        // echo "outbound end_ts [$end_ts] vs remote.first_ts [$first_ts], exiting...\n ";
        return 0;
     } 
     
     if ($start_ts > $ts)
      {
         echo "outbound start_ts [$start_ts], exiting... \n";        
         return -1;
      }

     
         
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
          
              
     //*/
     
     $max_select = 150000;
     
     $start_id = find_id($remote, $tsmap, $start_ts, true);
     $end_id   = find_id($remote, $tsmap, $end_ts, false);
     
     $range = "(ts >= '$start_ts') AND (ts < '$end_ts')";
     
     if ($start_id > 0 && $start_id < $end_id )
         $range = "( trade_id >= $start_id ) AND ( trade_id < $end_id) ";
     
         
     $strict = "WHERE $range";
     
     $local_cnt = $mysqli->select_value('COUNT(trade_id)', $pair, $strict); // WHERE (ts >= '$start_ts')  AND (ts <= '$end_ts') 
       
     $remote_cnt = $remote->select_value('COUNT(trade_id)', $pair, $strict);        
      
     $add_cnt = 0;
     
           
     $min_id = 0;                
     $diff = $remote_cnt - $local_cnt;
     echo " Checking date range from [$start_ts] .. [$end_ts] ($range), diff = $diff ";
     $remote_total += $remote_cnt;      
      
     while ($remote_cnt > $local_cnt) // $local_cnt < $remote_cnt     
     {
        
        echo " start from trade #$min_id ";
     
        if ($remote_cnt > 100000)
           echo " local ticks [$local_cnt], remote ticks <b>[$remote_cnt]</b>\n ";
        else        
           echo " local ticks [$local_cnt], remote ticks [$remote_cnt]\n ";
        // совмещение данных, через пересечение. При избыточном числе тиков за месяц, работать не будет!  
         
        $fields = 'trade_id,order_id,QUOTE(ts),price,flags,volume';       
        $strict = "WHERE (trade_id > $min_id) AND $range \n ORDER BY trade_id LIMIT $max_select;";
        
        $local_rows   = $mysqli->select_from($fields, $pair, $strict,  MYSQLI_USE_RESULT);
        if (!$local_rows) 
            die("#FATAL: cannot load local data");
        set_time_limit(3600);
        $old_data = array();
        $cnt_local = 0;
        
        $num_rows = $local_rows->num_rows;                 
        while ($num_rows > 0 && $row = $local_rows->fetch_array(MYSQLI_NUM))
        {
          $id = $row[0];
          $old_data[$id] = $row;
          $cnt_local ++;          
        }       
         
        $local_rows->close(); 
         
        if ($num_rows > 0) 
            log_msg("   loaded $num_rows/$cnt_local ticks from local DB");
                
        $add_data = array();        
        $checked = 0;
        // ob_flush();
        
        log_msg("loading remote data...");
                
        //  $strict = "WHERE (ts >= '$start_ts') AND (ts < '$end_ts')\n ORDER BY trade_id LIMIT $max_select;";
         
        $remote_rows = $remote->select_from($fields, $pair, $strict,  MYSQLI_USE_RESULT);        
                 
        $num_rows = $remote_rows->num_rows;
        if ($num_rows > 0) 
            log_msg("   loaded $num_rows/$remote_cnt ticks from remote DB");
        
        while ($row = $remote_rows->fetch_array(MYSQLI_NUM))
        {
          $id = $row[0];
          $checked ++;
          if (0 == $cnt_local || !isset($old_data[$id]))
          {             
             $line = implode($row, ',');
             $add_data []= "($line)";             
          }           
          $min_id = $id; // для повторения большого цикла
          
        }
        
        $remote_rows->close();
        
        $add_cnt = count($add_data);
        
        $fields = 'trade_id,order_id,ts,price,flags,volume';
        
        if ($add_cnt > 0) 
        {        
            log_msg("adding $add_cnt new ticks");
            // добавление исключительно отсутствующих данных, в конец таблицы (unsorted)            
            set_time_limit(200);
            $query = "INSERT IGNORE INTO $pair($fields)\nVALUES\n";
            batch_query($query, '', $add_data, 10000);
            
            $local_cnt += $add_cnt; 
                        
            $btable =  $pair.'__bars';
            //try_query("TRUNCATE TABLE $btable"); // clearing bars
            try_query("DELETE FROM $btable WHERE (ts >= '$start_ts')"); // partial clip
            
            flush();            
        } // if (add_cnt > 0)
        else
            log_msg(" checked $checked ticks, all present in local DB.");
        
     } // outer while, sync target  
     // else echo " local_cnt == remote_cnt == $local_cnt \n";
     echo "\n";


     return $add_cnt;    
  }

  function optimize($pair)
  {
    global $trades_fields, $mysqli;
    
    log_msg (" optimizing trades table for $pair... ");                        
    $fields = 'trade_id,order_id,ts,price,flags,volume';
    $table_new = "`$pair~new`";
    log_msg("creating temporary table $table_new");
    
    $nt = make_table_ex($table_new, $trades_fields, 'id', ", UNIQUE KEY `TID`(`trade_id`), KEY `TS` (`ts`)"); // ,`id`,  `order_id`
    
    var_dump($nt);
    
    if (!$nt)
    {
       die("<b>#FATAL: cannot create temporary table $table_new</b>");
       return false;
    }    
    // transfer data with reorder fields    
    try_query("TRUNCATE TABLE $table_new");
    
    $query = "INSERT INTO $table_new ($fields)\n";
    $query .= "SELECT $fields FROM $pair\n";
    $query .= "ORDER BY trade_id";
    set_time_limit(900);       
    if (try_query($query))
    {
       log_msg(" insert result: ".$mysqli->affected_rows);    
       if (try_query("DROP TABLE $pair")) 
           echo " [DROP TABLE $pair] - OK\n ";     
       $query = "RENAME TABLE $table_new TO $pair;";
       if (try_query($query))
           echo " [$query] - OK \n";  
    }  
                          
  } 

  log_msg("connecting to local host DB");
  init_db("trades_history");
  
  $pair = rqs_param('pair', '');

  if (isset($argv))
  {
   
     print_r ($argv);
     if ('' == $pair && isset($argv[1]))
         $pair = $argv[1];    
  }
  
   echo " connecting to [$db_alt_server]... ";
   $remote = new mysqli_ex($db_alt_server, $db_user, $db_pass);
   
   if (0 != mysqli_connect_errno())
       $remote = init_remote_db($db_user, $db_pass); // select one from accessible server
   
   if ($remote && 0 == mysqli_connect_errno())    
       echo " remote DB connected at $db_alt_server \n";
   else
   {
      log_msg(" failed connect to remote server $db_alt_server");
      die("#FATAL: cannot work without remote server.\n");
   }
   
  $remote->select_db('trades_history');
  
  $first_ts = false;
  
  function process($pair)
  {
     global $first_ts, $remote_total;
     $first_ts = false;
     
     $added = 0;
     $remote_total = 0;
     
     for ($m = 0; $m < 7 * 12; $m++)
     {
       $r =sync_for_pair($pair, $m);
       if ($r  < 0) break;
       $added += intval($r);
       echo " remote_total after $m+ = $remote_total \n";       
     }  
     
     echo " total added for $pair = $added \n ";
     if ($added > 1000)
         optimize($pair);      
  }
  
  
  if ($pair != 'all')
  {
     process($pair);
  }     
  else    
   foreach ($save_pairs as $pair)
   {  
      process($pair);     
   }  

  if ($mysqli) $mysqli->close();
  if ($remote) $remote->close();
  
  echo " script complete for [$pair] \n";  
?>
</pre>