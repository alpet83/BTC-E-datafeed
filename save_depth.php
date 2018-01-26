<?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  ob_implicit_flush();

  set_time_limit(150);
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  
  define('MIN_VOLUME', 0.005);
  // define('SQL_TIMESTAMP', 'Y-m-d H:i:s');
    

  $ldt = new DateTime('now');
  $sec = $ldt->format('s') * 1;
  
  
  if ($sec < 59)
  {
      log_msg ("time not actual ");
      if ($sec < 55)   
          sleep(55 - $sec);
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
    
  
  
  $date = utc_time(); // new DateTime ('now', new DateTimeZone('UTC'));
  $ldt->modify('now');   
  $ts = $date->format(SQL_TIMESTAMP); // GMT ts
  $sec  = ( $date->format('s') + 0 ); 
  $m    = ( $date->format('i') + 0 );
  if ($sec > 50) $m++;
  
  $last_ts = '';
  
  $pair = rqs_param('pair', '');
  if (strlen($pair) > 0)
      echo "<pre>";
    
  $pair_flt = '_';
    
  foreach ($argv as $arg)
  {  
    if (strpos($arg, '=') === false && $argc <= 2)
        $pair = $arg;
  
    if (strpos($arg, 'pair=') !== false)    
        $pair = str_replace('pair=', '', $arg);
        
    if (strpos($arg, 'pair_filter') !== false)    
        $pair_flt = str_replace('pair_filter=', '', $arg);    
        
  }

  if ($pair_flt == '_usd' xor ($m % 2 == 0))
     $api = 3;
  else
     $api = 4;
  
  $log_dir = "logs/save_depth_$pair";
  check_mkdir($log_dir);  
  $log_name = "$log_dir/$m.log";
  $log_file = fopen($log_name, "w");

  // log_msg(" ts = [$ts], m = [$m] ");  
  

  $depth_fields = array('id' => $id_field);
  $depth_fields['ts'] = 'timestamp(3) NULL';
  $depth_fields['price']  = $double_field;
  $depth_fields['volume'] = $double_field;
  $depth_fields['flags'] = 'int(11) NOT NULL';
  
  $stats_fields = array();
  $stats_fields['ts'] = 'timestamp NOT NULL';
  $stats_fields['best_ask'] = $float_field;
  $stats_fields['best_bid'] = $float_field;  
  $stats_fields['volume_asks'] = $float_field;
  $stats_fields['volume_bids'] = $float_field;  
  $stats_fields['cost_asks'] = $float_field;
  $stats_fields['cost_bids'] = $float_field;
  
  // echo "ID_FIELD:[$id_field]\n";
  
  $health_fields = array('id' => $id_field);
  $health_fields['pair']     = 'VARCHAR(7)';
  $health_fields['ts_period'] = 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
  $health_fields['full_updates']  = 'INT(11) unsigned';
  $health_fields['diff_updates']  = 'INT(11) unsigned';
   
  
  

  $spreads_fields = array(    
    'id' => 'int(11) UNSIGNED NOT NULL  AUTO_INCREMENT',
    'buy_0.1'  => $float_field,
    'buy_1'    => $float_field,
    'buy_10'   => $float_field,
    'buy_100'  => $float_field,
    'buy_1000' => $float_field,
    'buy_10k'  => $float_field,
    'sell_0.1' => $float_field,
    'sell_1'   => $float_field,
    'sell_10'  => $float_field,
    'sell_100'  => $float_field,
    'sell_1000' => $float_field,
    'sell_10k'  => $float_field,
    'ts' => 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP'); // ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

  $commits = 0; // сколько добавленно в таблицу дифф

  check_mkdir($date_dir);


  function load_last_ts($pair, $suffix)
  {
    global $mysqli;
    return $mysqli->select_value('ts', $pair.$suffix, "ORDER BY ts DESC");
  }

  function load_last_depth($pair, $suffix)
  {
    global $mysqli;
    $result = $mysqli->select_from('price, volume, ts', $pair.$suffix, "ORDER BY price\n"); 
    if (!$result) die("Failed load depth ");    
    return $result;
  } 
  
  function saldo_depth_volume($pair, $suffix, $calc_cost)
  {
    global $mysqli;
    $fields = 'volume';
    if ($calc_cost) 
        $fields = 'volume * price';
        
    $query = "SELECT SUM($fields) FROM $pair$suffix\n";
    $result = $mysqli->try_query($query); 
    if (!$result) die('DB query error');    
    return  $result->fetch_array ()[0];
  }
  

  function save_depth_stats($pair, $s, $ts)
  {     
    global $mysqli;
    $keys = array_keys($s);
    // echo(" price = {$rec[0]}  vol {$rec[1]} \n");
    $query = "INSERT IGNORE INTO $pair"."__stats\n";
    $query .= '(ts,'.join($keys, ',').")\n";
    $query .= "VALUES\n";
    $query .= "('$ts'";
    foreach ($keys as $k)
       $query .= ','.$s[$k];
       
    $query .= " )\n";
    $mysqli->try_query($query) or die("DB query failed");  
  }

  function fmt_depth_row($rec, $flags)
  { 
    global $ts;      
    return "('$ts',{$rec[0]},{$rec[1]},$flags)";      
  }


  function save_depth_row($pair, $table, $rec, $flags)
  {     
    global $mysqli;
    // single save
    $query = "INSERT INTO $pair$table\n";
    $query .= "(ts,price,volume,flags)\n";
    $query .= "VALUES". fmt_depth_row($rec, $flags);    
    $mysqli->try_query($query) or die("DB query failed");    
  }
  function save_diff_row($pair, $price, $vol, $flags)
  {
    global $ts, $mysqli; 
    $query = "INSERT INTO $pair"."__diff\n";
    $query .= "(ts,price,volume,flags)\n";
    $query .= "VALUES\n";
    $query .= "('$ts',$price,$vol,$flags)\n";
    $mysqli->try_query($query) or die("DB query failed");   	
  }
 
  function save_diff($pair, $old_res, $new, $suffix)
  {
     global $commits, $ts;
     if (0 == count($new)) return 0;

     $old = array();
     $s_date  = utc_time($ts);
     $s = $s_date->format('s');
     if ($s > 0 && $s < 15)    
         $s += 60;
     else
         $s = 60;            
     
     $ref = $s_date->sub(new DateInterval("PT55".'S'))->format(SQL_TIMESTAMP);
     echo " save_diff $pair, start ts [$ts], ref ts [$ref] \n";          
     $lch = $ref;

     while ($line = $old_res->fetch_array(MYSQLI_NUM))
     {     
        array_push($old, $line);
        $lts = str_replace('.000', '', $line[2]);        
        if ($lts > $lch)        
            $lch = $lts;           
     }   
     
     if ($lch > $ref)
     {
        echo "\t diff table last changed at [$lch], breaking diff detection\n";
        return 0;
     } 
     
     
     
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
         log_msg ("diff commits total = $commits ", "\n", true);
         
     return $commits;   
  } // save_diff

  function try_insert($query) // only for depth __ask/__bids tables
  {
    global $mysqli;
    $query = rtrim($query, ",\n");
    $query .= "\n ON DUPLICATE KEY UPDATE volume=VALUES(volume), ts=VALUES(ts)\n";
    
    $res = $mysqli->try_query($query);
    
    if ($res) 
       log_msg(" +added rows = ".$mysqli->affected_rows);
    else          
       die("non continuable error!\n");
  }

  function cleanup_table($pair, $suffix, $data, $total)
  {
     global $mysqli;
     if ( count($data->asks) < 2000 && count($data->bids) < 2000)
          $total = true;
  
     $tstart = pr_time();
     $lts = date('H:i:s');
     if ($total)
     {          
        list($usec, $sec) = explode(" ", microtime());
         
        // echo "[$lts]. #DBG: truncating table $pair$suffix...\n";    
        // $query = "TRUNCATE TABLE $pair$suffix";        
        $query = "DELETE FROM `$pair$suffix`";
        $result = $mysqli->try_query($query);
        if (!$result)
            echo("#ERROR: request [$query] failed in cleanup table\n");
                
        $info = sprintf(" query [$query] timing %.3f sec, %d rows affected", pr_time() - $tstart,  $mysqli->affected_rows);
        // echo "  $info \n";                
        log_msg($info);
     }
     else
     {
        $vals = $data->asks;
        if (strpos($suffix, 'bids') !== false)
            $vals = $data->bids;
            
        $min_v = $vals[0][0];                  
        $max_v = $vals[count($vals)- 1][0]; 
        
        if ($min_v > $max_v) // swap it, if wrong
        {
          $t = $max_v;
          $max_v = $min_v;
          $min_v = $t; 
        }   
        
        
        
        log_msg(" full data.$suffix range: $min_v .. $max_v ");                   
        if ($min_v > 0 && $min_v < $max_v)
        {            
            $query = "DELETE FROM $pair$suffix"." WHERE (price >= $min_v) and (price <= $max_v);";
            
            if (strpos($suffix, 'asks') !== false) // для асков удалить все данные, ниже наивысшего нового
                $query = "DELETE FROM $pair$suffix"." WHERE (price <= $max_v);";
            
            if (strpos($suffix, 'bids') !== false) // для бидов удалить все данные, выше наименьшего нового
                $query = "DELETE FROM $pair$suffix"." WHERE (price >= $min_v);";
            
            echo "[$lts]. #DBG: range [$min_v .. $max_v], query: \n\t $query\n";
        }    
        else                 
            $query = "TRUNCATE TABLE $pair$suffix";
            
        log_msg($query);    
        $result = $mysqli->try_query($query) or die("\n#ERROR: clean request failed");      
        
     }
     
  }

  function save_full_depth ($pair, $table, $data)
  {              
    global $ts, $m, $mysqli, $depth_fields;
    
    $ts   = $data->ts;
   	$asks = $data->asks;
   	$bids = $data->bids;
    $tmpt = $table.'_tmp';    
    // $mysqli->try_query("CREATE TABLE IF NOT EXISTS `$pair$tmpt` LIKE `$pair$table`;");
    make_table($pair.$tmpt, $depth_fields, ", UNIQUE KEY `OPT` (price), KEY `TIMESTAMP` (ts)", 'MEMORY');    
    // $mysqli->try_query("LOCK TABLES $pair$table WRITE");    
    $trunc = false;
    if (0 == $m) $trunc = true;
    if ( strpos($table, 'asks') !== false ) $trunc = true; // must be full replaced
    if ( strpos($table, 'bids') !== false ) $trunc = true; // must be full replaced        
    
    cleanup_table($pair, $tmpt, $data, $trunc);
    
    $tstart = pr_time();
    
    $head = "INSERT INTO $pair$tmpt\n";
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
    {
        try_insert($query);
    }    
    
    $trash = $table.'_old';
    
    $mysqli->try_query("RENAME TABLE `$pair$table` TO `$pair$trash`, `$pair$tmpt` TO `$pair$table`;");  
    // $mysqli->try_query("UNLOCK TABLES"); 
    $elps = pr_time() - $tstart;    
    $info = sprintf(" $pair$table depth was saved: ts = [$ts], asks = %5d, bids = %5d, skip = %4d], filter = %f, timing  = %.3f sec.\n", $acnt, $bcnt, $skip, MIN_VOLUME, $elps);
    
    $lts = date('H:i:s');
    echo("[$lts]. #OPT: $info");    
    log_msg ($info);
    $mysqli->try_query("DROP TABLE `$pair$trash`;");
            
  }
  
  
  include_once('spreads.inc.php');  
  
  function prepare($pair)
  {
     global $depth_fields, $stats_fields, $trades_fields; // ENGINE = MEMORY
     // make_table($pair."__temp",    $depth_fields, ", KEY `OPT` (ts,price)");
     make_table($pair."__full",    $depth_fields, ", KEY `OPT` (ts,price)");
     make_table($pair."__diff",    $depth_fields, ", KEY `OPT` (ts,price)"); 
     
     // try_query("DROP TABLE $pair"."__last");     
     make_table($pair."__asks",    $depth_fields, ", UNIQUE KEY `OPT` (price), KEY `TIMESTAMP` (ts)", 'MEMORY');
     make_table($pair."__bids",    $depth_fields, ", UNIQUE KEY `OPT` (price), KEY `TIMESTAMP` (ts)", 'MEMORY');     
     make_table_ex($pair."__stats",  $stats_fields, 'ts', false);
       
  }
     
  function load_data($pair)
  {
     global $ts, $api, $last_ts, $date_dir, $last_url;
     $path =  "/var/www/depth/";
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";    
     
     log_msg("request $pair depth data from exchange server via APIv$api");
     
     $txt = get_public_data('depth', $pair, $api, 'limit=10000');
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
     global $ts, $mysqli, $date, $last_ts, $date_dir, $last_url;
     
     
     // $mysqli->try_query($query);
     
     $pair = $data->pair;
     $ts = $data->ts;    
     $minute = $date->format('i') + 0;
     $hour_ts = $date->format('Y-m-d H:0:0');    
    

     $query = "INSERT IGNORE INTO `health`(pair,ts_period,diff_updates,full_updates)\n ";
     $query .= "VALUES('$pair', '$hour_ts', 0, 0)\n;";
     $mysqli->try_query($query);
     
     $upd_strict = "(pair = '$pair') AND (ts_period = '$hour_ts')";
          
     $save_full = false;
     $time_pass = ($minute < 5);
     
     if ($date->format('Y-m-d') >= '2017-01-20')
         $time_pass = ($minute % 15 == 0);
      
     
     if ($time_pass)   
     {
         $full_ts = $mysqli->select_value('ts', $pair.'__full', 'ORDER BY ts DESC');
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


     $commits = 0;
     // сохранение разницы между старыми и новыми таблицами
     $commits += save_diff($pair, $prv_asks, $data->asks, '__asks');
     $commits += save_diff($pair, $prv_bids, $data->bids, '__bids');
          
     if ($commits > 0)
     {     
       $query = "UPDATE `health`\n SET ts_period = ts_period, diff_updates = diff_updates + 1\n WHERE $upd_strict;";
       $mysqli->try_query($query);
     }  

     $acnt = count($data->asks);
     $wask = $data->asks[$acnt - 1][0];     
     $bcnt = count($data->bids);
     $wbid = $data->bids[$bcnt - 1][0];

     $prices = " BIDS: $wbid -> {$data->bids[0][0]}, ASKS: $wask -> {$data->asks[0][0]} ";
     echo "$prices\n";

  
     // log_msg("cleaning `last` tables for $pair"); 
          
     $sec = $date->format('s') * 1;
 
     log_msg('save_full_depth performing');
     
     save_full_depth($pair, "__asks", $data);
     save_full_depth($pair, "__bids", $data);
     
     on_data_update('save_depth', $ts);
     
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
       on_data_update('save_depth_full', $ts);       
       
       $query = "UPDATE `health`\n SET ts_period = ts_period, full_updates = full_updates + 1\n WHERE $upd_strict;";
       $mysqli->try_query($query); 
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
     // not need reverse
     save_spreads($pair, $data->asks, $data->bids);
     try_query('UNLOCK TABLES;');
           
	}

  
  init_db('depth_history');
  // $mysqli->try_query("DROP TABLE health;");
  
  make_table('health', $health_fields, ", UNIQUE KEY `complex` (pair,ts_period)");
    
  // foreach ($save_pairs as $pair) save_for_pair($pair);
  //*
  $start = time();
  
  $loaded = 0;
  
  
  
  
  $batch = array();
  if('all' == $pair) 
     $batch = $save_pairs;
  
  
  
  
  
  $data_map = array();
  
  $force = ($m == 0) || ($m == 30);
   
  if (strpos($pair, 'group') !== false) // group upload
  {
     $g = str_replace('group', '', $pair);   
     $rows = $mysqli->select_from('pair', 'pair_groups', "WHERE grp = $g" );
     if (!$rows) die('#FATAL: Not exists table `pair_groups`!');
     
     while ($row = $rows->fetch_array(MYSQLI_NUM))
            $batch[] = $row[0];
  } 
  else   
  if (strlen($pair) >= 7)
  {
     $batch[] = $pair;  
     log_msg(" single pair $pair update "); 
     // $data = load_data($pair);
     // save_for_pair($data, true); 
  }
  
  $count = count($batch); 
  $i = mt_rand(1, $count);
  
  printf ("[$ts]. #DBG: trying save $count pairs for request [$pair]: %s, start $i \n", join($batch, ','));

  if ($count <= 0) die("invalid pairs set specified!\n");
    
  // foreach ($save_pairs as $pair)
  while ($loaded < $count) 
  {     
     $i ++; $loaded ++;
     if ($i >= $count) $i = 0;     
     $elps = time() - $start;
           
     log_msg("[$loaded] elapsed $elps sec ================================================================================================== ");
     $pair = $batch[$i];
     
     if (!strpos($pair, $pair_flt)) 
     {
        echo " $pair ignored by filter! \n";
        continue;
     }
        
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
         
    // $mysqli->try_query("DROP TABLE $pair\x5f_old");
    // $mysqli->try_query("DROP TABLE $pair\x5f_nope");
    
    $date->modify('now');
    $mn = $m + 1;   
    save_for_pair($data, false);    
  }
  
  $mysqli->close();
  fflush($log_file);
  fclose($log_file);  
?>
