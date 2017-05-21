<?php
  header("Access-Control-Allow-Origin:*");
  $log_file = false;
   
  $cur_dir  = getcwd(); // '/var/www/btc-e';
                                  

  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  
  
  
  check_mkdir('./logs');
  check_mkdir('./tmp');
  
  $rqs = rqs_param('rqs', mt_rand()); // rand(1, getrandmax())
  
  $pid =  getmypid();
    
  $log_file = tmpfile(); //  fopen($log_name, "w");

  set_time_limit(10);
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  
  define('MIN_VOLUME', 0.005);
  
  $date = utc_time();
  $date_dir = "$cur_dir/depth/".$date->format('Ymd');
  $ts = $date->format('Y-m-d H:i:s');
  $last_ts = '';
  
  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";
  

  $depth_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $depth_fields['ts'] = 'timestamp NOT NULL';
  $depth_fields['price']  = $double_field;
  $depth_fields['volume'] = $double_field;
  $depth_fields['flags'] = 'int(11) NOT NULL';
  
  $stats_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $stats_fields['ts'] = 'timestamp NOT NULL';
  $stats_fields['best_ask'] = $float_field;
  $stats_fields['best_bid'] = $float_field;  
  $stats_fields['volume_asks'] = $float_field;
  $stats_fields['volume_bids'] = $float_field;  
  $stats_fields['cost_asks'] = $float_field;
  $stats_fields['cost_bids'] = $float_field;
  

  $spreads_fields = array(    
    'id' => 'int(11) UNSIGNED NOT NULL  AUTO_INCREMENT',
    'buy_0.1'  => $float_field,
    'buy_1'    => $float_field,
    'buy_10'   => $float_field,
    'buy_100'  => $float_field,
    'buy_1000' => $float_field,
    'sell_0.1' => $float_field,
    'sell_1'   => $float_field,
    'sell_10'  => $float_field,
    'sell_100'  => $float_field,
    'sell_1000' => $float_field,
    'ts' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'); // ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  $commits = 0; // сколько добавленно в таблицу дифф

  // check_mkdir($date_dir);

  
  $insert_cache = "";
  $insert_bids = array();
  $insert_asks = array();
  $d_updates   = array();
  $zero_volume = 0;
  
  foreach ($save_pairs as $i => $pair)  
  { 
     $d_updates[$pair] = 0; 
  }
  
  $pair = rqs_param('pair', '');
    
  function cache_insert($head, $row)
  {
     global $insert_cache;
     if ($insert_cache == "")
         $insert_cache = $head;
         
     $insert_cache .= "$row\n";   
  }
  

  function load_last_depth($pair, $suffix)
  {
    global $mysqli;
    $query = "SELECT price, volume FROM $pair$suffix\n"; 
    $query .= "ORDER BY price ";                         // самый майлый аск - первый
    // if (strpos($suffix, '__bids')) $query .= "DESC;";   // самый большой бид - первый
    // echo("Executing $query\n");
    $result = $mysqli->query($query); 
    if (!$result) die("Failed <$query> with errors:\n".$mysqli->error);    
    return $result;
  } 
  
  function saldo_depth_volume($pair, $suffix, $calc_cost)
  {
    global $mysqli;
    $fields = 'volume';    
    if ($calc_cost) 
        $fields = 'volume * price';
        
    $query = "SELECT SUM($fields) FROM $pair$suffix\n";
    $result = $mysqli->query($query); 
    if (!$result) die("Failed <$query> with errors:\n".$mysqli->error);    
    return  $result->fetch_array(MYSQL_NUM)[0];
  }
  

  function save_depth_stats($pair, $s)
  {
    global $mysqli, $ts;
    $keys = array_keys($s);
    // echo(" price = {$rec[0]}  vol {$rec[1]} \n");
    $query = "INSERT INTO $pair"."__stats\n";
    $query .= '(ts,'.join($keys, ',').")\n";
    $query .= "VALUES\n";
    $query .= "('$ts'";
    foreach ($keys as $k)
       $query .= ','.$s[$k];
       
    $query .= " )\n";
    $mysqli->query($query) or die("Failed <$query> with errors:\n".$mysqli->error);  
  }

  function fmt_depth_row($rec, $flags)
  { 
    global $ts;      
    return "('$ts',{$rec[0]},{$rec[1]},$flags)";      
  }

  function save_diff_row($pair, $price, $vol, $ts, $flags)
  {      
    $head = "INSERT INTO $pair"."__diff ";
    $head .= "(ts,price,volume,flags) ";
    $head .= "VALUES ";
    $row = "('$ts',$price,$vol,$flags),";
    cache_insert($head, $row);     	
  }
  
  function update_depth($table, $row, $flags)
  {
     global $log_file, $insert_asks, $insert_bids, $zero_volume;
     $query = '';
     $price = $row[0];
     $vol   = $row[1]; 
     $ts    = $row[2];
          
     $data   = "('$ts',$price,$vol,$flags)";        
     if (strpos($table, '_asks'))
       $insert_asks []= $data;
     else
       $insert_bids []= $data;
    
     if ($vol <= MIN_VOLUME)    
         $zero_volume ++;
         
     
     return true;
     
  }
  
  
  function exec_multi($query, $context)
  { 
     global $mysqli;     
     $query = rtrim($query, ",;\n");      
     log_msg("[$context] query size: ".strlen($query));
     // log_msg($query);
     if ($mysqli->multi_query($query))
     {
         $cnt = 0;                  
         do 
         {   $result = $mysqli->store_result();
             $cnt ++;
             if ($result) log_msg(" result[$cnt]: ".var_dump($result));                
         } while ($mysqli->next_result());
         log_msg("[$context] query sucess, results [$cnt] ");             
     }
     else
         log_msg("[$context] query failed\n $query\n mysql error:".$mysqli->error);
   
  }
  
  function insert_last($pair, $suffix, $rows)
  {
    global $mysqli, $zero_volume;
    $count = count($rows);
    if (0 == $count) return false;
           
           
    $query = "INSERT INTO $pair$suffix(ts,price,volume,flags) VALUES ";
    $query .= implode($rows, ",\n");
    $query .= " ON DUPLICATE KEY UPDATE volume=VALUES(volume), ts=VALUES(ts)";
    exec_multi($query, "insert into $pair$suffix = $count ");
    
    if (0 == $zero_volume) return true;
    
    $query = "DELETE FROM $pair$suffix";                 
    $query .=  sprintf(' WHERE volume <= %.5f ', MIN_VOLUME);
    $r = $mysqli->try_query($query);
    if ($r)
        log_msg(" from $pair$suffix removed [{$mysqli->affected_rows}] rows");                                       
     
    return true;
  }
  
  
  function insert_diff($pair, $data = false)
  {
     global $ts, $mysqli, $log_file, $insert_cache, $insert_asks, $insert_bids, $zero_volume;     
     if (!$data) 
     {
        $data = $_POST['data'];
        if (!$data) die("#FATAL: no data for insert_diff");  
        $data = json_decode($data);     	
     } 
     
     log_msg("insert_diff to $pair ================================================================================ ");
     
     $zero_volume = 0;
     
     // print_r($data);
     $vals = array();
          
     $query = '';
     $count = 0;         
     $min_price = 1e100;
     $max_price = 0;
          
     if (isset ($data->ask))
     {
        $table = $pair.'__asks';
        $ask = $data->ask;
        foreach ($ask as $row)
        {
           fprintf($log_file, "\tchanging ask: %s \n", implode($row, ','));
           if (update_depth($table, $row, 2))
           {                  
               save_diff_row($pair, $row[0], $row[1], $row[2], 2);
               $min_price = min($min_price, $row[0]);
           }    
           $count ++;
        }        
     }
     if (isset ($data->bid))
     {
        $table = $pair.'__bids';
        $bid = $data->bid;
        foreach ($bid as $row)
        {
           fprintf($log_file, "\tchanging bid: %s \n", implode($row, ','));
           if (update_depth($table, $row, 1))        
           {
               save_diff_row($pair, $row[0], $row[1], $row[2], 1);
               $max_price = min($max_price, $row[0]);
           }   
           $count ++;
        }        
     }
  
     if (0 == $count) return; 
  
     insert_last($pair, '__asks', $insert_asks);
     insert_last($pair, '__bids', $insert_bids);
     
     exec_multi($insert_cache, "insert diff");
     // try_query( 'UNLOCK TABLES' );
     $v = $mysqli->select_value('MIN(price)', "$pair\137_asks");
     if ($v) $min_price = min($min_price, $v); 
     $v = $mysqli->select_value('MAX(price)', "$pair\137_bids");     
     if ($v) $max_price = max($max_price, $v); 
     
     log_msg("  min(asks) = $min_price, max(bids) = $max_price "); 
     
     if ($min_price > 0)             
         $mysqli->try_query("DELETE FROM $pair\137_bids WHERE price >= $min_price"); // в бидах не должно быть высоких цен  
     if ($max_price > $min_price)             
         $mysqli->try_query("DELETE FROM $pair\137_asks WHERE price <= $max_price"); // в асках не должно быть низких цен  
             
     $query_cache = "";
     $insert_cache = "";
     $insert_asks = array();
     $insert_bids = array();
    
  
     return $count;
  }
  
  include_once('spreads.inc.php');   

  function load_remote_depth($pair)
  {
     global $conn_attemtps, $mysqli, $mysqli_remote, $last_ts, $db_alt_server, $db_user, $db_pass;
      
     $last_ts = $mysqli->select_value('ts' , "$pair\137_diff", 'ORDER BY ts DESC');
     
     $date = utc_time($last_ts);
     
     if (time() - $date->getTimestamp() < 10)
     {
        log_msg("load remote depth ignored, due data actual");
        return; // local data was actual
     }
     
     $sec = $date->format('i') * 1;
      
      
     if (!$mysqli_remote)
     {        
        if ($conn_attemtps < 10 || ($sec > 40 && $sec < 50) )
        {
          printf("[%s] #DBG: attempt #$conn_attemtps reconnect to remote DB...\n ", $date->format('H:i:s'));     
          $mysqli_remote = init_remote_db ($db_user, $db_pass);
          $conn_attemtps ++;
        }  
     
     } 
       
       
     $remote = $mysqli_remote;
     
     
     if ($remote && 0 == mysqli_connect_errno())
     {
        $conn_attemtps = 0;
        $date->setTimestamp( time() - 5 ); // go to past        
        $limit = $date->format('Y-m-d H:i:s');
     
     
        log_msg("#OPT($pair): checking data on remote server $db_alt_server  ...");
        $fields = 'ts, price, volume, flags';
        $query  = "SELECT $fields FROM trades_history.$pair\n";
        $query .= " WHERE (ts > '$last_ts') and (ts < '$limit') \n";
        $query .= ' ORDER BY ts';        
        log_msg($query);
        $result = $remote->try_query($query);
        
        $lines = array();
         
        if ($result)        
        while($row = $result->fetch_array(MYSQL_NUM))
        {             
           $row[0] = "'".$row[0]."'";
           $l = '('.implode($row, ',').')';
           // print_r($row);
           //echo "$l \n";                   
           $lines []= $l;
        } 
        else
          log_msg(" query [$query] failed with error: ".$remote->error);
        $remote->close();
          
        // log_msg('#PERF: request complete!');  
          
        if (count($lines) > 0)
        {  
          // print_r($lines);
          $query = "INSERT INTO $pair\137_diff ($fields) VALUES\n";
          $query .= implode($lines, ",\n");
          log_msg(" adding remote quotes: ".count($lines));
          // echo "$query\n";
          $mysqli->try_query($query);   
        }  
                
                
     }     
     else
        log_msg(" failed connect to remote server $db_alt_server");
  
  }
  
  

  function update_for_pair($pair, $force = false, $data = false)
  {
     global $ts, $date, $mysqli, $last_ts, $date_dir, $depth_fields, $stats_fields, $trades_fields, $last_url, $d_updates;
     
     $mysqli->select_db('depth_history');
     $d_updates[$pair] ++;
     
     if ($d_updates[$pair] < 5)
     {
        $mysqli->try_query("DROP TABLE IF EXISTS $pair\137_last;");
     }
     
     $date = utc_time();
     $ts = $date->format('Y-m-d H:i:s');
     $last_ts = $ts;
     
     $path =  "/var/www/depth/";
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";
     
     load_remote_depth($pair);
          
     $upd = insert_diff($pair, $data);
     if ($upd >= 0)
     {
         log_msg("stored $upd rows");
         on_data_update('depth_diff_upd', $ts);                 
     }    
     else
         log_msg("update_for_pair: failed load from data:\n ".print_r($data, true)); 
     
     // return true;
     // $query = sprintf("RENAME TABLE `depth_history`.`%s_full` TO `depth_history`.`%s__full`;\n", $pair, $pair);
     // log_msg ($query);
     
     $save_full = false;
     $minute = $date->format('i') + 0;
     $last_ts = $mysqli->select_value('ts' , "$pair\137_full", 'ORDER BY ts DESC');  
     
     if ($last_ts && strlen($last_ts) > 7 && $minute % 15 == 0)
     {
         $dt = utc_time($last_ts);
         $table = $pair.'__full';
         
         $full_ts = $mysqli->query("SELECT ts FROM $table ORDER BY ts DESC LIMIT 1");
         if ($full_ts)
         { 
             $full_ts = $full_ts->fetch_array(MYSQL_NUM)[0];
             // if (is_array($full_ts)) $full_ts = $full_ts[0];        
         }                
         if ($full_ts && strlen($full_ts) > 7)
         {           
            $dt->modify($full_ts);                
            $last_stm = $dt->format('Y-m-d H:i');
            $curr_stm = $date->format('Y-m-d H:i'); 
            
            log_msg ("#CHECK_HR: full snapshot at $full_ts, round '$last_stm' vs '$curr_stm' \n");
            $save_full = ($last_stm != $curr_stm); // полный стакан сохраняется каждый чос
         }
     }

     // сохранение разницы между старыми и новыми таблицами
     // save_diff($pair, $prv_asks, $tab->$pair->asks, 2);
     // save_diff($pair, $prv_bids, $tab->$pair->bids, 1);
     
     if ($upd)
     {
     
        if ($save_full)
        { 
          // save_full_depth($pair, "__full", $tab);
          $fields = 'ts,price,volume,flags';          
          
          $query = "INSERT INTO $pair"."__full($fields)\n";
          $query .= "SELECT $fields FROM $pair"."__bids\n"; 
          $mysqli->query($query);
          $query = "INSERT INTO $pair"."__full($fields)\n";
          $query .= "SELECT $fields FROM $pair"."__asks\n"; 
          
                   
          log_msg("#PERF_WARN: full snapshot also saved! \n"); 
        }          
        
        $sec = $date->format('s') + 0;
        
        $add_stats = ($sec % 10 == 0);
        
        $last_ts = $mysqli->select_value('ts', "$pair\137_stats", 'ORDER BY ts DESC');
        if ($last_ts)
        {        
           $last_ts = utc_time($last_ts);
           $elps = $date->getTimestamp() - $last_ts->getTimestamp();
           log_msg(" stats was updated $elps seconds ago");
           if ($elps >= 30) 
               $add_stats = true;            
        }
        
        if ( $add_stats )
        {
          $stats = array();    
          $asks = array();
          $bids = array();
          log_msg(" creating stats...");
          // загрузка предыдущих бидов и асков
          $last_asks = load_last_depth($pair, '__asks');
          $last_bids = load_last_depth($pair, '__bids');
          // оба массива отсортированы по возрастанию цены(!)
          
          
          while ($row = $last_asks->fetch_array(MYSQL_NUM))          
                 $asks []= $row;
          while ($row = $last_bids->fetch_array(MYSQL_NUM))          
                 $bids []= $row;
          
          
              
          $stats['cost_asks']   = saldo_depth_volume($pair, '__asks', true);
          $stats['cost_bids']   = saldo_depth_volume($pair, '__bids', true);
          $stats['volume_asks'] = saldo_depth_volume($pair, '__asks', false);
          $stats['volume_bids'] = saldo_depth_volume($pair, '__bids', false);
          $stats['best_ask'] = 0;
          $stats['best_bid'] = 0;
          
          
          if (isset($asks[0]))
              $stats['best_ask'] = $asks[0][0];
              
          if (count($bids) > 0)    
              $stats['best_bid'] = $bids[count($bids) - 1][0];
          
                                
          
          if ($stats['best_bid'] > 0 && $stats['best_bid'] < $stats['best_ask'])
          {
              log_msg(" spread: {$stats['best_bid']} .. {$stats['best_ask']}. Saldo volume bids = {$stats['volume_bids']}, bids = {$stats['volume_asks']} \n");      
              save_depth_stats ($pair, $stats);
          }    
          else
          {
              log_msg("invalid stats: ". print_r($stats, true));
              log_msg("#WARN: save_depth_stats not performed ");





              $first_bid = $bids[0][0];
              $last_bid  = $bids[count($bids) - 1][0];
              log_msg(" first_bid: $first_bid, last_bid: $last_bid"); 
              
          }
          
          $bids = array_reverse($bids);          
          save_spreads($pair, $asks, $bids);          
          // cleanup
        }                
     }
     
          
     log_msg("#COMPLETE:-------------------------------------------- )");		
     
	}
    
  // foreach ($save_pairs as $pair) update_depth($pair);
  //*
  function update_log($pair, $open_new = false)
  {
    global $log_file, $cur_dir;
    
    fflush($log_file);  
    fseek($log_file, 0);
    $c = fread($log_file, 65536);     
    
    $f = fopen("$cur_dir/logs/upd_depth_$pair.log", 'a+');
    fclose($log_file);
    
    fputs($f, $c);
    fflush($f);    
    fclose($f);
    $log_file = $open_new ? tmpfile() : false;     
  }
  
  function complex_update($data)
  {
     global $log_file, $session_logs, $mysqli, $cur_dir, $pid, $log_name, $d_updates;
     if ($log_file) fclose($log_file);
     
     $log_file = tmpfile();
     $log_name = "$cur_dir/logs/cmpx_depth_$pid.log";
     // if (array_search($log_name, $session_logs) === false)
     $session_logs [$log_name]= true; // updated    
     
     $fh = fopen($log_name, "a+");
     
     fprintf($fh, str_ts_sq()." updating for all pairs \n");
     $start = precise_time();         
          
         
     
     foreach($data as $pair => $rec)
     if ($rec->ask || $rec->bid)
     {
       // log_msg("data [$pair]:\n".print_r($rec,true)); 
       fprintf($fh, str_ts_sq()."\t processing $pair \n");
       update_for_pair($pair, true, $rec);              
       update_log("$pair+", true);
     } 
     $end = precise_time();
     
     $elps = diff_time_ms($start, $end);     

     fprintf($fh, " stats updates by pair:\n %s \n", print_r($d_updates, true));
     fprintf($fh, str_ts_sq()." elps = %.3f ms (%f -> %f) ----------------------------------------------------------------------- \n", 
                          $elps, $end[1], $start[1]);
     fclose($fh);  
  }

   
  if (strlen($pair) >= 7)
  {  
     init_db('depth_history');
     update_for_pair($pair, true); 
  }
  else
  if ($pair == 'all') //  && isset($_POST['data'])
  {
     init_db('depth_history');
     set_time_limit(25);
     $data = $_POST['data'];
     $data = json_decode($data);
     complex_update($data);      
  }
  
  if ($mysqli) $mysqli->close();  
  update_log($pair or 'nope');
  // unlink($log_name);  
?>
