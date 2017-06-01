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
    
  $date_dir = "$tmp_data_dir/trades/".$date->format('Ymd');
  $ts = $date->format('Y-m-d H:i:s');

  $trades_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $trades_fields['ts'] = 'timestamp NOT NULL';
  $trades_fields['price']    = $double_field;
  $trades_fields['order_id'] = 'bigint(20) NOT NULL default 0';
  $trades_fields['trade_id'] = 'bigint(20) NOT NULL';
  $trades_fields['flags']    = 'int(11) unsigned NOT NULL';
  $trades_fields['volume']   = $double_field;

  $bars_fields = array('id' => 'int(11) unsigned NOT NULL AUTO_INCREMENT');
  $bars_fields['ts'] = 'timestamp NULL DEFAULT NULL';
  $bars_fields['open']       = $double_field;
  $bars_fields['high']       = $double_field;  
  $bars_fields['low']        = $double_field;
  $bars_fields['close']      = $double_field;
  $bars_fields['volume']     = $double_field;
  $bars_fields['last_trade'] = 'bigint(20) NOT NULL';

  function save_bars($pair, $force)
  {  
     global $log_file, $mysqli, $bars_fields;
     $table =  $pair.'__bars';     
     $dir = "logs/trades";
     check_mkdir($dir);

     $log_file = fopen("$dir/bars_$pair.log", "a+");

     log_msg("#DBG(save_bars): getting data for $pair...");
     $tmp = $mysqli->try_query("SHOW CREATE TABLE $table");
     $row = $tmp->fetch_array(MYSQLI_NUM);
     if ($row && strpos($row[1], 'CURRENT_TIMESTAMP'))
     {          
        log_msg("WRONG SQL:\n" .$row[1]);
        $mysqli->try_query("DROP TABLE $table");
        make_table($table, $bars_fields, ", UNIQUE KEY `TIMESTAMP`(`ts`) ");
     }

     $columns = '`ts`,`open`,`high`,`low`,`close`,`volume`,`last_trade`';
     $last_bar = $mysqli->select_row($columns, $table, 'ORDER BY ts DESC');
     $count = 0;   
     
     if (!$last_bar)
     {
        log_msg(" select_row returned: ".var_dump($last_bar));
        $last_bar = array("2001-01-01 00:00:00", 0, 0, 0, 0, 0, -1);

        if ($mysqli->select_value('COUNT(id)', $table) > 0)
        {
           log_msg(" fake last_bar used, mysql error: ".$mysqli->error);
           fclose($log_file); $log_file = false;
           return;
        }
     }

     $last_bts   = $last_bar[0];
     $last_trade = $last_bar[6];

     if (!$last_trade) $last_trade = 0;

     log_msg("last bar time = [$last_bts], tick = [$last_trade]");


     $fields = 'ts,price,trade_id,volume';
     $ticks = $mysqli->select_from($fields, $pair, "WHERE trade_id > $last_trade ORDER BY trade_id");
     if (!$ticks)
     {
        log_msg('#FATAL(save_bars): no ticks returned!');
        fclose($log_file); $log_file = false;
        return;
     }

     $new_ticks = $ticks->num_rows;

     log_msg(" retrived [$new_ticks] ticks after [$last_trade] ");

     if (0 == $new_ticks) return false;

     $tz = new DateTimeZone('UTC');
     $bars = array();
     $tformat = "Y-m-d H:i:00";

     $date    = new DateTime($last_bar[0], $tz);
     $last_ts = $date->format($tformat);
     

     $prev_ts = ''; 
     //               O1 H2 L3 C4 V  L6
     $bar = array("2001-01-01 01:01:00", 0, 0, 0, 0, 0, 0);
     // processing all ticks 
       
     $ts = new DateTime('now', $tz);  
     while ($row = $ticks->fetch_array(MYSQLI_NUM))
     {
       $count ++;       
       $ts->modify($row[0]);
       
       $tts = $ts->format($tformat);
       
       if ($tts == $last_ts)
       {                  
          $bar = $last_bar; // adding new ticks to bar
          $prev_ts = $last_ts;          
          $values = implode($bar, ',');
          // log_msg ( sprintf("last_bar [%s], adding tick [%s], \n", $values, implode($row, ',')) );
       }
       
       $price = $row[1];
       $id  = $row[2]; 
       $vol = $row[3];
                              
       if ($prev_ts == $tts) // updating prev bar  
       {         
          $bar[2] = max($bar[2], $price);
          $bar[3] = min($bar[3], $price);
          $bar[4] = $price;
          $bar[5] = $bar[5] + $vol;
          $bar[6] = $id; // last_trade
          if ($tts == $last_ts && $id >$last_bar[6])
              $last_bar = $bar; 
       }
       else
       {   
          // never add last_bar, it must be updated!
          if ($bar[1] > 0 && $bar[0] != $last_bts)
          {
              $bar[0] = "'{$bar[0]}'";
              $values = implode($bar, ',');
              if (count($bars) < 10) log_msg(" [$last_bts] added new bar: $values");
              // if ($tts >= '2017-01-17 22:47:00') printf("generated bar [%s], last added tick [%s], \n", $values, implode($row, ',')); 
              $bars []= "($values)";
          }
       
          $bar = array($tts, $price, $price, $price, $price, $vol, $id);          
       }          
       
       $prev_ts = $tts; 
                   
     } // while


     if ($bar[1] > 0 && $bar[0] != $last_bts)
     {
         $bar[0] = "'{$bar[0]}'";
         $bars []= '('. implode($bar, ',') .')'; // last uncomplete bar add
     }
      
     $cnt = count($bars);
     
     if ($last_bar[1] > 0)
     {
        $b = $last_bar;
        $query = "UPDATE $table\n";
        $query .= "SET high={$b[2]}, low={$b[3]}, close={$b[4]}, volume={$b[5]}, last_trade={$b[6]}\n";
        $query .= "WHERE ts='{$last_ts}'";
        echo "$query\n\n";       
        
        $mysqli->try_query($query);   
     }
        
     $added = 0;      
     $limit = 5000;
     
     while ( count($bars) > 0 )  
     {
        log_msg ("parsed [$count] ticks, generated [$cnt] new bar(s) \n");
        $query = "INSERT INTO $table($columns)\nVALUES\n";
        
        $slice = array();                             
                              
        if (count($bars) <= $limit)
        {
          $slice = $bars;
          $bars = array();
        }    
        else   
           $slice = array_splice($bars, 0, $limit);                                         
        
        $query .= implode($slice, ",\n");        
	      $query .= " ON DUPLICATE KEY UPDATE close=VALUES(close), volume=VALUES(volume)\n";
	      
        $add = count($slice);              
        
        if ($add < $cnt)
            echo ("trying insert $add/$cnt rows in $table \n");
        
        if($mysqli->try_query($query))
           $added += $add; 
        else           
         {
            log_msg(" on error: cleanup table tail... ");
            $mysqli->try_query("DELETE FROM $table WHERE last_trade >= $last_trade");
         }


          // $mysqli->try_query("TRUNCATE TABLE $table"); // need
     }
     
      
     fclose($log_file); $log_file = false;
  } // save_bars


  function save_trades($pair, $force)
  {
     global $log_file, $ws_recv, $mysqli, $btce_api, $trades_fields, $date, $last_url, $db_alt_server, $db_user, $db_pass, $debug;
     $old_id = 0;
     $mysqli->select_db('trades_history');
     if ($debug)
         echo " save_trades for pair [$pair] \n";

     $dir = "logs/trades";
     check_mkdir($dir);
     if ($ws_recv)
        $log_file = fopen("$dir/ws_save_$pair.log", "a+");
     else
        $log_file = fopen("$dir/save_$pair.log", "a+");
     make_table_ex("$pair", $trades_fields, 'trade_id', ", KEY `SCAN` (`id`, `ts`, `order_id`)");

     // опредление, какие данные в наличии.
     
     $need_full = false;
     $last_ts = false;

     $row = $mysqli->select_row('trade_id, ts', $pair, 'ORDER BY trade_id DESC');         
     if ($row)
     {
       $old_id = $row[0];
       $last_ts = $row[1]; 
     }


     $date = utc_time();
     $remote = init_remote_db($db_user, $db_pass); // select one from accessible server
     if ($remote && 0 == mysqli_connect_errno())
     {
        log_msg("#OPT($pair): have trades with id < $old_id, checking data on remote server $db_alt_server  ...");
        $fields = 'ts, price, order_id, trade_id, flags, volume';
        $query = "SELECT $fields FROM trades_history.$pair\n";
        $query .= "WHERE trade_id > $old_id";        
        $result = $remote->try_query($query);
        
        $lines = array();
         
        if ($result)        
        while($row = $result->fetch_array(MYSQLI_NUM))
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
          $query = "INSERT INTO $pair ($fields) VALUES\n";
          $query .= implode($lines, ",\n");
          // echo "$query\n";
          $mysqli->try_query($query);
          $result = $mysqli->try_query("SELECT trade_id, ts FROM $pair ORDER BY trade_id DESC LIMIT 1") or die("failed SELECT query : ".mysql_error());
          $row = $mysqli->select_row('trade_id, ts', $pair, 'ORDER BY trade_id DESC');
          if ($row)
          { 
            $old_id = $row[0];
            $last_ts = $row[1];
            log_msg("#OPT($pair): updated last trade_id = [$old_id], time = [$last_ts]"); 
          }   
        }  
                
                
     }     
     else
     {
       log_msg(" failed connect to remote server $db_alt_server");       
     }

     $remote = null;
     $upd_age = 100; // как давно обновлялось
          
     if (strlen($last_ts) > 10)
     {     
        $ref_dt = utc_time($last_ts);             
        $diff = date_diff($date, $ref_dt);        
        $upd_age = $diff->s + ($diff->h * 60 + $diff->i) * 60;
        log_msg("#OPT: last_ts [$last_ts] "); // .print_r($diff, true)
        log_msg("#OPT($pair): local data age $upd_age seconds \n"); 
     }
     
     $params = '';
     $lazy = strpos($pair, "dsh_") || strpos($pair, "nvc_") || strpos($pair, "ppc_") || strpos($pair, "nmc_");
     
          
     echo "old_id = $old_id \n";     
          
     if ($old_id == 0 || $upd_age > 100)
       {     
         $params = 'limit=500';
         if ($upd_age > 300) $params = 'limit=1000';
         if ($upd_age > 600) $params = 'limit=2000';      
         if ($upd_age > 900) $params = 'limit=5000';
       }          
     else
     {   // обычно мало сделок бывает, при частых запросах
         if ($upd_age < 60)
            $params = 'limit=50'; 
         
         if ( $force && $lazy || $upd_age < 10)
            $params = 'limit=10';            
     }    

     if (!$force && $upd_age < 90 && $old_id > 0)
     {
        log_msg("#OPT($pair): request to exchange not need, due data have actual state ");
        fclose($log_file); $log_file = false;
        return true;    
     }


     log_msg(" loading trades from btc-e, using public APIv$btce_api");
     echo ("[$pair]. #DBG: upd_age = $upd_age, after '$last_ts'. Using params [$params]\n");     
     $txt = get_public_data('trades', $pair, $btce_api, $params);
     if (trim($txt) == "")
     {
        log_msg(" failed get_public_data for $last_url");
        fclose($log_file); $log_file = false;
        return false;  
     }

     $path =  getcwd()."/trades/";
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";
     file_put_contents($file_name,  $txt);

     $tab = json_decode($txt);

     log_msg("#DBG: previuos saved trade for $pair = $old_id \n");

     if (isset($tab) && isset($tab->$pair) )  // typical ->btc_usd
     {
        $trades = array_reverse($tab->$pair);
        log_msg(" loaded lines from [$last_url]: ".count($trades));

        $count = 0;
        $query = "INSERT INTO $pair (ts, price, trade_id, flags, volume)\n VALUES\n";

        foreach ($trades as $rec)
        {
           // print_r($rec);
           $flags = 0;
           if ($rec->type == 'bid') $flags |= 1;
           $date->setTimestamp($rec->timestamp);
           $ts = $date->format('Y-m-d H:i:s');
           $price = $rec->price;
           $vol   = $rec->amount;
           $tid   = $rec->tid;
           if ($tid <= $old_id) continue;

           $count ++;
           if ($count > 1) $query .= ",\n";

           $query .= "\t('$ts', $price, $tid, $flags, $vol)";
        }

        if ($count > 0)
        {
           // echo("trades add: $query \n");
           $mysqli->query($query);
           on_data_update('save_trades', $ts);
        }

     }

     fclose($log_file); $log_file = false;
  } // save_trades


  $pair = rqs_param('pair', '');
  if ('' == $pair && isset($argv[1]))
      $pair = $argv[1]; 

  echo "[$ts]. save_trades.php pair = [$pair] \n";
   
  if (strlen($pair) >= 7)
  {
     init_db("trades_history");
     save_trades($pair, true);
     save_bars($pair, true);  
  }
  else
  if ($pair == 'all')  
  {
     init_db("trades_history");
     foreach ($save_pairs as $pair)
     {
        save_trades($pair, false);
        save_bars($pair, false);
     } // foreach
  }  

  if ($mysqli) $mysqli->close();
  echo " script complete for pair [$pair] \n";
?>
