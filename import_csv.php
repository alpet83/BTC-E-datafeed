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
    
  $date_dir = getcwd()."/trades/".$date->format('Ymd');
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

  function save_bars($pair, $new_suffix = false)
  {  
     global $mysqli, $bars_fields;
     $table =  $pair.'__bars';
     if ($new_suffix) 
         $table .= '_new';
          
     $dir = "logs/trades";
     check_mkdir($dir);

     // $log_file = fopen("$dir/bars_$pair.log", "a+");

     log_msg("#DBG(save_bars): getting data for $pair...");
     
     $tmp = $mysqli->try_query("SHOW CREATE TABLE $table");
     $row = $tmp->fetch_array(MYSQL_NUM);
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
           return;
        }
     }
     
      
     $last_bts   = $last_bar[0];
     $last_trade = $last_bar[6];

     if (!$last_trade) $last_trade = 0;

     log_msg("last bar time = [$last_bts], tick = [$last_trade]");


     $fields = 'ts,price,trade_id,volume';
     $src_table = $pair;
     
     if ($new_suffix) 
         $src_table .= '__new'; 
     
     $ticks = $mysqli->select_from($fields, $src_table, "WHERE trade_id > $last_trade ORDER BY trade_id");
     if ($ticks === false)
     {
        log_msg('#FATAL(save_bars): no ticks returned!');        
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
              // if (count($bars) < 10) log_msg(" [$last_bts] added new bar: $values");
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
          
  } // save_bars

  init_db("trades_history"); 

 
  $files = array();  
  
  $src_dir = '/mnt/ram/';
  
  $d = dir($src_dir);
  echo "Handle: " . $d->handle . "\n";
  echo "Path: " . $d->path . "\n";
  while (false !== ($entry = $d->read())) {
     echo $entry."\n";
     if (strpos($entry, '.csv') !== false) 
         $files []= $src_dir . $entry; 
  }
  $d->close();
  
  $save_pairs = array_reverse($save_pairs);
  
  foreach ($save_pairs as $pair)
  {
    $btable = "$pair\137_bars_new";
    make_table($btable, $bars_fields, ", UNIQUE KEY `TIMESTAMP`(`ts`) ");
    $table = "$pair\137_new";
    make_table_ex($table, $trades_fields, 'trade_id', ", KEY `SCAN` (`id`, `ts`, `order_id`)");
      
    $head = "INSERT IGNORE INTO $table (ts, price, trade_id, flags, volume)\n";
    
    $data = array();
    
    foreach ($files as $fn)
     if (strpos($fn, $pair) !== false)
     {
        log_msg(" loading data from $fn... ");
        $data = file($fn);
        break;
     }
     
    $limit = 10000;
    /*
      trade_id, b/s, amount, price, timestamp
      1,buy,0.77900000,10.40000000,1 313 331 280
      2,buy,0.10100000,10.40000000,1313334917
      3,buy,0.31600000,10.40000000,1313334917
    
    //*/
    
    // inserting by portion
    
    $old_id = -1;
    
    $lcount = count($data);
    log_msg("loaded $lcount lines. ");
    
    
    while ($lcount > 0)
    {
        set_time_limit(30);
    
        $slice = array();                            
                              
        if (count($data) <= $limit)
        {
          $slice = $data;
          $data = array();
        }    
        else   
           $slice = array_splice($data, 0, $limit);
             
        $query = $head. " VALUES\n";     
        
        $lcount = count($data);
        $count = 0;
             
        foreach ($slice as $line)
        {
           // print_r($rec);
           $vals = explode(',', $line); 
            
           $flags = 0;
           if ($vals[1] == 'bid') $flags |= 1;
           $ts = intval($vals[4]);
           if ($ts < 1300000000) continue;           
           $date->setTimestamp($ts);
           $ts = $date->format('Y-m-d H:i:s');
           
           $tid   = $vals[0];           
           $price = $vals[3];
           $vol   = $vals[2];
           
           if ($tid <= $old_id) continue;
           
           $old_id = $tid;

           $count ++;
           if ($count > 1) 
               $query .= ",\n";
               
           $query .= "\t('$ts', $price, $tid, $flags, $vol)";
        }           
                       
        $qsize = count($slice);
                               
        log_msg("trying insert $qsize rows into $table...");
        // echo $query;
       set_time_limit(300);
        
        if ($mysqli->try_query($query))
        {
          $rows = $mysqli->affected_rows;        
          if ($rows > 0)
          {
              printf(" affected rows $rows \n");
              save_bars($pair, true);
          }    
        }    
        
                                          
    } // inner loop 
    
    $data = null;
    
    $last_tid = $mysqli->select_value ('trade_id', $table, 'ORDER BY trade_id DESC');
    
    if ($last_tid) 
    {
        // log_msg(" last trade in $table = $last_tid ");      
        $query = $head . " SELECT ts, price, trade_id, flags, volume FROM $pair\n WHERE trade_id > $last_tid;";
        log_msg("trying query:\n ". $query);
        if ($mysqli->try_query($query))
        {
           $rows = $mysqli->affected_rows;        
           if ($rows > 0)
           {
             printf(" affected rows $rows \n");
             save_bars($pair, true);
           }    
        }
        // set_time_limit(1000);
        // echo "optimizing table $table... \n";    
        // $mysqli->try_query("OPTIMIZE TABLE $table;");                        
        $mysqli->try_query("RENAME TABLE `$pair` TO `$pair\137_old`, `$table` TO `$pair`;");
        $mysqli->try_query("RENAME TABLE `$pair\137_bars` TO `$pair\137_bars_old`, `$btable` TO `$pair\137_bars`;");
        
    }

    $mysqli->try_query("DROP TABLE IF EXISTS `$pair\137_new`;");
    $mysqli->try_query("DROP TABLE IF EXISTS `$pair\137_old`;");    
    $mysqli->try_query("DROP TABLE IF EXISTS `$pair\137_bars_new`;");
    $mysqli->try_query("DROP TABLE IF EXISTS `$pair\137_bars_old`;");
    
           
    
    // die("\n debug here.\n");  
    // echo "$query \n";
    
  } // outer loop

  
  if ($mysqli)
      $mysqli->close(); 

?>