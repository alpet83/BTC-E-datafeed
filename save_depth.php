<pre><?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');

  set_time_limit(150);
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  
  define('MIN_VOLUME', 0.005);
  

  $ldt = new DateTime('now');
  $sec = $ldt->format('s') * 1;

  
  if ($sec < 59)
  {
      log_msg ("time not actual ");
      if ($sec < 50)   
          sleep(10);
      else 
          sleep(1);    
      return;
  } 

  list($usec, $sec) = explode(" ", microtime());
  $usec *= 1000000;      
  $adj = 999990;       
  if ($usec < $adj) 
      usleep($adj - $usec + 00);

  $api = mt_rand(3, 4);

  $date_dir = "/var/www/depth/".$ldt->format('Ymd');  
    
  
  
  $date = new DateTime ('now', new DateTimeZone('UTC'));
  $ldt->modify('now');   
  $ts = $date->format('Y-m-d H:i:s'); // GMT ts
  $sec  = ( $date->format('s') + 0 ); 
  $m    = ( $date->format('i') + 0 );
  if ($sec > 50) $m++;
  
  $last_ts = '';
  
  $pair = rqs_param('pair', '');  
  $pair_flt = '_';
    
  foreach ($argv as $arg)
  {  
    if (strpos($arg, 'pair=') !== false)    
        $pair = str_replace('pair=', '', $arg);
        
    if (strpos($arg, 'pair_filter') !== false)    
        $pair_flt = str_replace('pair_filter=', '', $arg);    
        
  }

  if ($pair_flt == '_usd' xor ($m % 2 == 0))
     $api = 3;
  else
     $api = 4;
  
  $log_dir = "logs/save_depth$pair_flt";
  check_mkdir($log_dir);  
  $log_name = "$log_dir/$m.log";
  $log_file = fopen($log_name, "w");

  // log_msg(" ts = [$ts], m = [$m] ");  
  
  $double_field = "double NOT NULL DEFAULT '0'";
  $float_field  = "float NOT NULL DEFAULT '0'";
  

  $depth_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $depth_fields['ts'] = 'timestamp(3) NULL';
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

  check_mkdir($date_dir);


  function load_last_ts($pair, $suffix)
  {
    return select_value('ts', $pair.$suffix, "ORDER BY ts DESC");
  }

  function load_last_depth($pair, $suffix)
  {
    $result = select_from('price, volume', $pair.$suffix,"ORDER BY price\n"); 
    if (!$result) die("Failed load depth ");    
    return $result;
  } 
  
  function saldo_depth_volume($pair, $suffix, $calc_cost)
  {
    $fields = 'volume';
    if ($calc_cost) 
        $fields = 'volume * price';
        
    $query = "SELECT SUM($fields) FROM $pair$suffix\n";
    $result = mysql_query($query); 
    if (!$result) die("Failed <$query> with errors:\n".mysql_error());    
    return  mysql_fetch_array ( $result )[0];
  }
  

  function save_depth_stats($pair, $s, $ts)
  {     
    $keys = array_keys($s);
    // echo(" price = {$rec[0]}  vol {$rec[1]} \n");
    $query = "INSERT INTO $pair"."__stats\n";
    $query .= '(ts,'.join($keys, ',').")\n";
    $query .= "VALUES\n";
    $query .= "('$ts'";
    foreach ($keys as $k)
       $query .= ','.$s[$k];
       
    $query .= " )\n";
    mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());  
  }

  function fmt_depth_row($rec, $flags)
  { 
    global $ts;      
    return "('$ts',{$rec[0]},{$rec[1]},$flags)";      
  }


  function save_depth_row($pair, $table, $rec, $flags)
  {     
    // single save
    $query = "INSERT INTO $pair$table\n";
    $query .= "(ts,price,volume,flags)\n";
    $query .= "VALUES". fmt_depth_row($rec, $flags);    
    mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());    
  }
  function save_diff_row($pair, $price, $vol, $flags)
  {
    global $ts; 
    $query = "INSERT INTO $pair"."__diff\n";
    $query .= "(ts,price,volume,flags)\n";
    $query .= "VALUES\n";
    $query .= "('$ts',$price,$vol,$flags)\n";
    mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());   	
  }
 
  function save_diff($pair, $old_res, $new, $suffix)
  {
     global $commits;
     if (0 == count($new)) return;

     $old = array();

     while ($line = mysql_fetch_array($old_res, MYSQL_NUM))     
             array_push($old, $line);
     
     $flags = 2;
     
     if (strpos($suffix, 'bids'))
     {
	       $new   = array_reverse($new);
         $flags = 1;
     }
      
     $price = $new[0][0];
     if (count($old))
         $price = min($price, $old[0][0]);        
         
         
     log_msg(" $pair.min_price = $price \n");
     $i_old = 0;
     $i_new = 0;
     
     while ($i_old < count($old) && $i_new < count($new)) 
     {
	      $price_old = $old[$i_old][0];
        $price_new = $new[$i_new][0];
	      $vol_old = $old[$i_old][1];
        $vol_new = $new[$i_new][1];
        
        if ($vol_old < MIN_VOLUME) $vol_old = 0;
        if ($vol_new < MIN_VOLUME) $vol_new = 0;
        
        if ($price_old == $price_new)
        	{
        	   if ($vol_old != $vol_new)
               {
        	       // volume for this depth row was changed
          	     save_diff_row($pair, $price_new, $vol_new, $flags);
                 if ($pair == 'nvc_usd') log_msg("  *** @$price_new volume changed to $vol_new \n");
                 $commits ++;  
               }
        	   
        	   $i_old ++; $i_new ++; 
        	   continue;
        	}
       if ($price_old > $price_new)
       {
          // появился новый элемент стакана, с меньшей ценой
          if ($pair == 'nvc_usd') log_msg (" +++ new row added $price_new $vol_new \n");                     
          if ($vol_new > 0) save_diff_row($pair, $price_new, $vol_new, $flags);
          $commits ++;
          $i_new ++;
       }  
       else
       {
          // исчез старый элемент стакана с меньшей ценой
          if ($pair == 'nvc_usd') log_msg (" --- old row deleted $price_old $vol_old \n");
          save_diff_row($pair, $price_old, 0, $flags);
          $commits ++;
          $i_old ++;
       }
         
     } // while
     
     if ($commits > 0)
         log_msg ("diff commits total = $commits \n");
  } // save_diff

  function try_insert($query) // only for depth __ask/__bids tables
  { 
    $query = rtrim($query, ",\n");
    $query .= "\n ON DUPLICATE KEY UPDATE volume=VALUES(volume), ts=VALUES(ts)\n";
    
    $res = try_query($query);
    
    if ($res) 
       log_msg(" +added rows = ".mysql_affected_rows());
    else          
       die("non continuable error!\n");
  }

  function cleanup_table($pair, $suffix, $data, $total)
  {
     if ( count($data->asks) < 2000 && count($data->bids) < 2000)
          $total = true;
  
  
     if ($total)
     {    
        $query = "TRUNCATE TABLE $pair$suffix";        
        $result = try_query($query) or die("#ERROR: request <$query> failed:\n ".mysql_error());
        log_msg(" $query affected rows: ".mysql_affected_rows());
     }
     else
     {
        $vals = $data->asks;
        if (strpos($suffix, 'bids'))
            $vals = $data->bids;
            
        $min_v = $vals[0][0];                  
        $max_v = $vals[count($vals)- 1][0];
        
        if ($min_v > $max_v)
        {
          $t = $max_v;
          $max_v = $min_v;
          $min_v = $t; 
        }   
        
        log_msg(" full data range: $min_v .. $max_v ");                   
        if ($min_v > 0 && $min_v < $max_v)
            $query = "DELETE FROM $pair$suffix"." WHERE (price >= $min_v) and (price <= $max_v);";
        else                 
            $query = "TRUNCATE TABLE $pair$suffix";
            
        log_msg($query);    
        $result = try_query($query) or die("\n#ERROR: clean request failed");
     }
     
  }

  function save_full_depth ($pair, $table, $data)
  {              
    global $ts, $m;
    
    $ts   = $data->ts;
   	$asks = $data->asks;
   	$bids = $data->bids;
 
    try_query("LOCK TABLES $pair$table WRITE");
    cleanup_table($pair, $table, $data, 0 == $m);
    
    $head = "INSERT INTO $pair$table\n";
    $head .= "(ts,price,volume,flags)\n";
    $head .= "VALUES";       
  
    $acnt = 0;
    $bcnt = 0;
    $query = '';
  
    $skip = 0;
    if (strpos($table, 'asks'))
    {    
        log_msg("(save_full_depth) formating asks... ");
        // log_msg(" how in table $pair$table rows: ".select_value('COUNT(id)', $pair.$table));
        $query = $head;
     	  foreach ($asks as $a)    
        { 
          if ($a[1] >= MIN_VOLUME)
             $query .= fmt_depth_row($a, 2).",\n";
          else 
             $skip ++;
          $acnt ++;    
          if ($acnt % 500 == 0)
          {
             try_insert($query);
             $query = $head;
          }
            
        }
    }
      	 // save_depth_row($pair, $table, $a, 2);  
    
    if (strpos($table, 'bids'))
    {
        log_msg("(save_full_depth) formating bids... ");
        // log_msg(" how in table $pair$table rows: ".select_value('COUNT(id)', $pair.$table));
        $query = $head;
        $bids = array_reverse($bids);
        
        foreach ($bids as $b)
        {
           if ($b[1] >= MIN_VOLUME)
             $query .= fmt_depth_row($b, 1).",\n";
          else
             $skip ++;
          $bcnt ++;
                
          if ($bcnt % 500 == 0)
          {
             try_insert($query);
             $query = $head;
          }
        }            
    }
      	 // save_depth_row($pair, $table, $b, 1);
    if (strlen($query) > strlen($head) + 10)
        try_insert($query);
        
    try_query("UNLOCK TABLES"); 
        
    log_msg (sprintf(" full depth was saved: ts = [$ts], asks = [$acnt], bids = [$bcnt], skip = [$skip], filter = %f \n", MIN_VOLUME));
            
  }
  
  
  function fix_prices($price, $vol, &$dest)
  {    
     if ($vol < 0.1)  $dest[0] = $price; 
     if ($vol < 1.0)  $dest[1] = $price; 
     if ($vol < 10)   $dest[2] = $price;
     if ($vol < 100)  $dest[3] = $price;
     if ($vol < 1000) $dest[4] = $price;
     
     return $dest;   
  }


  function save_spreads($pair, $data)
  {
     global $ts, $spreads_fields;
     $asks = $data->asks;
     $bids = $data->bids;
     
     // $query = sprintf("ALTER TABLE `%s__spreads` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT", $pair);
     // if (strpos($ts, '18:30:')) try_query("TRUNCATE TABLE $pair".'__spreads');
     make_table($pair."__spreads", $spreads_fields, ", UNIQUE KEY `TIMESTAMP` (`ts`)");
     
     $query = "INSERT INTO $pair".'__spreads (';
     $query .= '`buy_0.1`, buy_1, buy_10, buy_100, buy_1000,';
     $query .= '`sell_0.1`, sell_1, sell_10, sell_100, sell_1000, ts)';
     $query .= "\n VALUES(";    
     
     
     $prices = array(0, 0, 0, 0, 0);
     $lp    = 0;
     $saldo = 0;
     foreach ($asks as $a)
     {
       $lp = $a[0]; 
       fix_prices($lp, $saldo, $prices);
       $saldo += $a[1]; // add volume
       fix_prices($lp, $saldo, $prices);
       if ($saldo > 1000) break;
     }

     $query .= implode(',', $prices);

     // printf(" $pair buy levels: [%s] ", implode(',', $prices)); 
     
     $saldo = 0;     
          
     
     foreach ($bids as $b)
     {        
     
       fix_prices($b[0], $saldo, $prices  );
       $saldo += $b[1]; // add volume
       fix_prices($b[0], $saldo, $prices);
       if ($saldo > 1000) break;
     }
     
     $query .= ','.implode(',', $prices);
     $query .= ",'$ts');\n";
      
     try_query($query);
     //printf(" sell levels: [%s] \n", implode(',', $prices));
  
  }
  
  
  function prepare($pair)
  {
     global $depth_fields, $stats_fields, $trades_fields;
     // make_table($pair."__temp",    $depth_fields, ", KEY `OPT` (ts,price)");
     make_table($pair."__full",    $depth_fields, ", KEY `OPT` (ts,price)");
     make_table($pair."__diff",    $depth_fields, ", KEY `OPT` (ts,price)"); 
     
     // try_query("DROP TABLE $pair"."__last");     
     make_table($pair."__asks",    $depth_fields, ", UNIQUE KEY `OPT` (price), KEY `TIMESTAMP` (ts)");
     make_table($pair."__bids",    $depth_fields, ", UNIQUE KEY `OPT` (price), KEY `TIMESTAMP` (ts)");     
     make_table($pair."__stats",   $stats_fields, false);
       
  }
     
  function load_data($pair)
  {
     global $ts, $api, $last_ts, $date_dir, $last_url;
     $path =  "/var/www/depth/";
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";    
     
     log_msg("request $pair depth data from exchange server via APIv$api");
     $txt = get_public_data('depth', $pair, $api, 'limit=2000');
     log_msg("from $last_url received data size: ".strlen($txt));
     file_put_contents($file_name,  $txt);     
     $tab = json_decode($txt);
     
     $date = new DateTime ('now', new DateTimeZone('UTC'));
     
     $ts = $date->format('Y-m-d H:i:s'); // effective time of load
    
     $data = false;
     if (isset($tab) && isset($tab->$pair))
         $data = $tab->$pair;
     else
         die("invalid data received from server: [$txt]");
     $data->ts = $ts;
     $data->pair = $pair;
     return $data;
  }
  
  function check_updated($pair)
  {
     global $date;
     
     $last_ts = min ( load_last_ts($pair, '__asks'), load_last_ts($pair, '__bids') );
     if ($last_ts == '') return false;
     
     $dt = new DateTime($last_ts, new DateTimeZone('UTC'));
     $tsl  = $dt->getTimestamp();                  
       
     $elps = $date->getTimestamp() - $tsl;
     log_msg("$pair last depth update was $elps seconds ago @'$last_ts' ");
    
     return ($elps <= 30);
  
  }



  function save_for_pair($data, $force = false)
  {
     global $ts, $date, $last_ts, $date_dir, $last_url;
     $pair = $data->pair;
     $ts = $data->ts;    
     
     $minute = $date->format('i') + 0;     
          
     $save_full = false;
     $time_pass = ($minute < 5);
     
     if ($date->format('Y-m-d') >= '2017-01-20')
         $time_pass = ($minute % 15 == 0);
      
     
     if ($time_pass)   
     {
         $full_ts = select_value('ts', $pair.'__full', 'ORDER BY ts DESC');
         $dt = new DateTime($full_ts, new DateTimeZone('UTC'));                  
         $full_stm = $dt->format('Y-m-d H:i');
         $curr_stm = $date->format('Y-m-d H:i');          
         log_msg ("last full snapshot at $full_ts, round '$full_stm' vs '$curr_stm' \n");         
         $save_full = ($full_stm != $curr_stm); // полный стакан сохраняется каждый чос
     }
     // $query = sprintf("RENAME TABLE `depth_history`.`%s_full` TO `depth_history`.`%s__full`;\n", $pair, $pair);
     // log_msg ($query);
     
     log_msg("detecting diff rows");
     // загрузка предыдущих бидов и асков
     
     // try_query("LOCK TABLES $pair".'__asks READ,WRITE');
     // try_query("LOCK TABLES $pair".'__bids READ,WRITE');
     
     $prv_asks = load_last_depth($pair, '__asks');
     $prv_bids = load_last_depth($pair, '__bids');


     // сохранение разницы между старыми и новыми таблицами
     save_diff($pair, $prv_asks, $data->asks, '__asks');
     save_diff($pair, $prv_bids, $data->bids, '__bids');

     mysql_free_result($prv_asks);
     mysql_free_result($prv_bids);

  
     log_msg("cleaning `last` tables for $pair");
  
          
     $sec = $date->format('s') * 1;
 
     log_msg('save_full_depth performing');
     
     save_full_depth($pair, "__asks", $data);
     save_full_depth($pair, "__bids", $data);
     if ($save_full)
     { 
       $fields = 'ts,price,volume,flags';
       $query = "INSERT INTO $pair"."__full($fields)\n";
       $query .= "SELECT $fields FROM $pair"."__bids\n"; 
       try_query($query);
       $query = "INSERT INTO $pair"."__full($fields)\n";
       $query .= "SELECT $fields FROM $pair"."__asks\n"; 
       try_query($query);
       log_msg ("$pair-full snapshot also added! \n"); 
     }          
    
     if ($sec < 10)
     {
        log_msg("calc stats performing");        
        $stats = array();
        $stats['cost_asks']   = saldo_depth_volume($pair, '__asks', true);
        $stats['cost_bids']   = saldo_depth_volume($pair, '__bids', true);
        $stats['volume_asks'] = saldo_depth_volume($pair, '__asks', false);
        $stats['volume_bids'] = saldo_depth_volume($pair, '__bids', false);
        $stats['best_ask'] = $data->asks[0][0];
        $stats['best_bid'] = $data->bids[0][0];            
        log_msg(" spread: {$stats['best_ask']} .. {$stats['best_bid']}. Saldo volume asks = {$stats['volume_asks']}, bids = {$stats['volume_bids']} \n");
        log_msg(print_r($stats, true));
        save_depth_stats ($pair, $stats, $data->ts);
     }    
     log_msg("calc & saving spreads");
     save_spreads($pair, $data);
     try_query('UNLOCK TABLES;');
           
	}

  $link = mysql_connect('localhost', $db_user, $db_pass) or die('cannot connect to DB server: '.mysql_error());
  mysql_select_db("depth_history") or die('cannot select DB depth_history');
  // foreach ($save_pairs as $pair) save_for_pair($pair);
  //*
  $start = time();
  
  $loaded = 0;
  
  $count = count($save_pairs);
  
  $data_map = array();
   
  $i = mt_rand(1, $count);
  $force = ($m == 0) || ($m == 30);
   
   
  if (strlen($pair) >= 7)
  {
     log_msg(" single pair $pair update "); 
     $data = load_data($pair);
     save_for_pair($data, true); 
  }
  else  
  // foreach ($save_pairs as $pair)
  while ($loaded < $count) 
  {     
     $i ++; $loaded ++;
     if ($i >= $count) $i = 0;     
     $elps = time() - $start;
           
     log_msg("[$loaded] elapsed $elps sec ================================================================================================== ");
     $pair = $save_pairs[$i];
     if (!strpos($pair, $pair_flt)) continue;
        
     prepare($pair); 
         
     if (check_updated($pair) && !$force)
     {
       log_msg("#OPT($pair): possible data is actual, full update not need\n-----------------------------------------------------------");
       continue;
     }    
     
     $data = load_data($pair);
     $data_map[$pair] = $data;   
     
  }
  
  foreach ($data_map as $data)
  {  
    $elps = time() - $start;
    $pair = $data->pair;    
    log_msg("[$pair] elapsed $elps sec saving >>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> ");
         
    // mysql_query("DROP TABLE $pair\x5f_old");
    // mysql_query("DROP TABLE $pair\x5f_nope");
    mysql_query("DROP TABLE $pair\x5f_temp");
         
    $tmp = try_query("SHOW CREATE TABLE $pair".'__diff');
    $row = mysql_fetch_array($tmp, MYSQL_NUM);
    if ($row && false === strpos($row[1], 'timestamp(3)') && $m % 10 == 2 )
    {
      $q = "ALTER TABLE `$pair\137_diff` CHANGE `ts` `ts` TIMESTAMP(3) NULL";
      log_msg($q);
      try_query($q);
    }    
    else
      log_msg($row[1]); 
    
    $date->modify('now');
    $mn = $m + 1;   
    save_for_pair($data, false);    
  }
  
  mysql_close($link);
  fflush($log_file);
  fclose($log_file);  
?>
