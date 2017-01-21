<?php
  header("Access-Control-Allow-Origin:*");
  
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');                                                                 
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  $date = utc_time();

  set_time_limit(30);
    
  $date_dir = "/var/www/btc-e/trades/".$date->format('Ymd');
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
     global $mysqli, $bars_fields;
     $table =  $pair.'__bars';     
     
     log_msg("#DBG(save_bars): getting data for $pair...");
     $tmp = $mysqli->try_query("SHOW CREATE TABLE $table");
     $row = $tmp->fetch_array(MYSQL_NUM);
     if ($row && strpos($row[1], 'CURRENT_TIMESTAMP'))
     {          
        log_msg("WRONG SQL:\n" .$row[1]);
        $mysqli->try_query("DROP TABLE $table");         
     }
     
     make_table($table, $bars_fields, ", UNIQUE KEY `TIMESTAMP`(`ts`) ");
          
     $last_trade = $mysqli->select_value('last_trade', $table, 'ORDER BY id DESC');     
     
     if (!$last_trade) $last_trade = 0;
     
     $fields = 'ts,price,trade_id,volume';
     $ticks = $mysqli->select_from($fields, $pair, "WHERE trade_id > $last_trade ORDER BY trade_id");
     if (!$ticks)
     {
        log_msg('#FATAL(save_bars): no ticks returned!');
        return;
     }
     
     $new_ticks = $ticks->num_rows;
     
     log_msg(" retrived [$new_ticks] ticks after [$last_trade] ");
     
     if (0 == $new_ticks) return false;
     
     $columns = '`ts`,`open`,`high`,`low`,`close`,`volume`,`last_trade`';
     $last_bar = $mysqli->select_row($columns, $table, 'ORDER by id DESC'); 
     $count = 0;   
     
     if (!$last_bar)
          $last_bar = array("2001-01-01 00:00:00", 0, 0, 0, 0, 0, 0);
     
     $tz = new DateTimeZone('UTC');
     
     $bars = array();     
     $tformat = "Y-m-d H:i:00";
     
     
     $date    = new DateTime($last_bar[0], $tz);
     $last_ts = $date->format($tformat);
     
     log_msg("last bar time = [$last_ts]");     
     $prev_ts = ''; 
     //               O1 H2 L3 C4 V  L6
     $bar = array("2001-01-01 01:01:00", 0, 0, 0, 0, 0, 0);
     // processing all ticks 
       
     $ts = new DateTime('now', $tz);  
     while ($row = $ticks->fetch_array(MYSQL_NUM))
     {
       $count ++;       
       $ts->modify($row[0]);
       
       $tts = $ts->format($tformat);
       
       if ($tts == $last_ts)
       {                  
          $bar = $last_bar; // adding new ticks to bar
          $prev_ts = $last_ts;          
          $values = implode($bar, ',');
          printf("last_bar [%s], adding tick [%s], \n", $values, implode($row, ',')); 
          
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
          if ($bar[1] > 0 && $bar[0] != $last_ts)
          {
              $bar[0] = "'{$bar[0]}'";
              $values = implode($bar, ',');
              // if ($tts >= '2017-01-17 22:47:00') printf("generated bar [%s], last added tick [%s], \n", $values, implode($row, ',')); 
              $bars []= "($values)";
          }
       
          $bar = array($tts, $price, $price, $price, $price, $vol, $id);          
       }          
       
       $prev_ts = $tts; 
                   
     } // while
     if ($bar[1] > 0)
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
           
     if ($cnt > 0 && $cnt < 10000)
     {
        log_msg ("parsed [$count] ticks, generated [$cnt] new bar(s) \n");
        $query = "INSERT INTO $table($columns)\nVALUES\n";
        $query .= implode($bars, ",\n");        
        echo "$query\n\n";
        if (!$mysqli->try_query($query))
             $mysqli->try_query("TRUNCATE TABLE $table"); // need      
     }
      
     
     
  }    


  function save_trades($pair, $force)
  {
     global $mysqli, $btce_api, $trades_fields, $date, $last_url, $db_alt_server, $db_user, $db_pass;
     $old_id = 0;
     $mysqli->select_db('trades_history');
     
     

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

     $remote = new mysqli_ex($db_alt_server, $db_user, $db_pass);
     
     $date = utc_time();
     
     
     if ($remote && 0 == mysqli_connect_errno())
     {
        log_msg("#OPT($pair): have trades with id < $old_id, checking data on remote server $db_alt_server  ...");
        $fields = 'ts, price, order_id, trade_id, flags, volume';
        $query = "SELECT $fields FROM trades_history.$pair\n";
        $query .= "WHERE trade_id > $old_id";        
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
        log_msg(" failed connect to remote server $db_alt_server");

     $remote = null;
     $upd_age = 100; // как давно обновлялось
          
     if (strlen($last_ts) > 10)
     {     
        $ref_dt = new DateTime($last_ts);             
        $diff = date_diff($date, $ref_dt);        
        $upd_age = $diff->s;
        log_msg("#OPT($pair): local data age $upd_age seconds \n"); 
         
     }
     
     $params = '';
     $lazy = strpos($pair, "dsh_") || strpos($pair, "nvc_") || strpos($pair, "ppc_") || strpos($pair, "nmc_");
     
     
     if ($old_id == 0 || $upd_age > 100)
         $params = '?limit=2000';
     else
      if ( $force && $lazy )
         $params = '?limit=10'; // обычно мало сделок бывает.             
         

     if (!$force && $upd_age < 90 && $old_id > 0)
     {
        log_msg("#OPT($pair): request to exchange not need, due data have actual state");     
        return true;    
     }



     $txt = get_public_data('trades', $pair, $btce_api, $params);
     if (trim($txt) == "")
     {
        log_msg(" failed get_public_data for $last_url");
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
           echo("trades add: $query \n");
           $mysqli->query($query);
        }

     }


  }



  $pair = rqs_param('pair', '');
  if ('' == $pair && isset($argv[1]))
      $pair = $argv[1]; 
   
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
?>
