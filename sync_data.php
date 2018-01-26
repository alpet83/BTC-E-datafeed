<pre>
<?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');                                                                 
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  $date = utc_time();
  $m    = ( $date->format('i') + 0 );

  $sfields = 'ts,best_ask,best_bid,volume_asks,volume_bids,cost_asks,cost_bids';
  
  $db_name = 'depth_history';
  $remote = init_remote_db($db_user, $db_pass); // select one from accessible server
  if (!$remote)die('non-continuable error');
  
  $remote->select_db($db_name);
  
  $stats_fields = array();
  $stats_fields['ts'] = 'timestamp NOT NULL';
  $stats_fields['best_ask'] = $float_field;
  $stats_fields['best_bid'] = $float_field;  
  $stats_fields['volume_asks'] = $float_field;
  $stats_fields['volume_bids'] = $float_field;  
  $stats_fields['cost_asks'] = $float_field;
  $stats_fields['cost_bids'] = $float_field;
  
  //            0    1          2       3         4         5
  $sp_fields = 'ts, `buy_0.1`, buy_1, buy_10, buy_100, buy_1000, buy_10k,';
  //            6             7       8         9         10
  $sp_fields .= '`sell_0.1`, sell_1, sell_10, sell_100, sell_1000, sell_10k';

  
  function sync_for_pair($pair, $suffix, $fields)  
  {
    global $mysqli, $remote, $sfields, $m, $date;
    
    log_msg("sync_for_pair <b>[$pair]</b> --------------------------------");
    
    
    
    set_time_limit(300);
    $table = "$pair\137_$suffix";

    if ($m <= 15)
        $ts_from = '2017-06-01 00:00:00';
    else
        $ts_from = $date->format('Y-m-d H:00:00'); 
    $ins = 0;       
    $line = 0;
    
    /*
    if ($suffix == 'spreads')
    {
      $query = "ALTER TABLE `$table` ADD `buy_10k` FLOAT NOT NULL DEFAULT '0' AFTER `buy_1000`;\n";
      $mysqli->try_query($query);
      $remote->try_query($query);
          
      $query = "ALTER TABLE `$table` ADD `sell_10k` FLOAT NOT NULL DEFAULT '0' AFTER `sell_1000`;\n";
      $mysqli->try_query($query);
      $remote->try_query($query);
    }
    

    // $mysqli->try_query(" ALTER TABLE `$table` ADD INDEX(`ts`)");
    // $remote->try_query(" ALTER TABLE `$table` ADD INDEX(`ts`)");
    /*
    $query = "ALTER TABLE `$table` DROP INDEX `ts`";
    // , ADD UNIQUE `ts` (`ts`) USING BTREE                                                        
    $mysqli->try_query($query);      
    $remote->try_query($query);
    
    $i = 0;
    for ($i = 6; $i < 7; $i++)
    {
      $query = "ALTER TABLE `$table` DROP INDEX `ts_$i`";
      $mysqli->query($query);
      $remote->query($query); 
    }
    //*/
    
    
    // return true;

    while (true)
    {
      /*
      log_msg("preclean spreads");
      $query = "DELETE FROM `$table` WHERE `sell_0.1` * 5 < `buy_0.1` ";
      $laff = 0;
      $raff = 0;            
      if ($mysqli->try_query($query))
      {
          $laff = $mysqli->affected_rows;
          log_msg(" removed rows $laff");
      }   
            
      if ($remote->try_query($query))
      {
          $raff = $remote->affected_rows;      
          log_msg(" removed rows $raff");
      }     
      if ($laff + $raff > 0)  break;
      //*/
    
      log_msg("loading local data from $table after [$ts_from]...");
      $limit = 'LIMIT 25000';
      $ldata = $mysqli->select_from($fields, $table, "WHERE ts > '$ts_from' ORDER BY ts $limit");    
      if (!$ldata) 
      {
         log_msg('#FATAL: select_from method failed '.$mysqli->error);
         return false;
      }
      
      $lsize = $ldata->num_rows;
      if (0 == $lsize) break; // complete op     
      
      log_msg('loading remote data...');
      $limit = 'LIMIT 15000';
      $rdata = $remote->select_from($fields, $table, "WHERE ts > '$ts_from' ORDER BY ts $limit\n");
      if (!$rdata) 
      {
         log_msg('#FATAL: select_from method failed '.$remote->error);
         return false; 
      }
      
      $rsize = $rdata->num_rows;
      
      if (0 == $rsize) break; // no data
      
      log_msg("<font color='#a010a0'>local rows [$lsize], source rows [$rsize]</font>");
       
      
      $lr = $ldata->fetch_array(MYSQLI_NUM);       
              
      $ldate = utc_time($lr[0]);
      $rdate = utc_time($lr[0]);
        
      set_time_limit(30);           
      // цикл поиска пропусков в локальных данных
          
      $lines = array();           
          
      while (true)
      {      
        $rr = $rdata->fetch_array(MYSQLI_NUM);      
        if (!$rr) break;
       
        $ts_from = $rr[0];
        // пропуск, пока локальные данные имеют меньшую дату, чем удаленные

        while ($rr && $lr && $lr[0] < $rr[0]) 
           $lr = $ldata->fetch_array(MYSQLI_NUM);
        if (!$lr) break;   
        
        $ldate->modify($lr[0]);
        $rdate->modify($rr[0]);                                     
        $dsec = abs($ldate->getTimestamp() - $rdate->getTimestamp());
                  
                     
        if ( $dsec < 30 ) continue; // небольшой пробел, можно не заполнять        
        if ($line++ % 500 == 0) 
            echo " compare [{$lr[0]}] vs [{$rr[0]}] diff = [$dsec] [".implode($rr, ',')."]\n";
        
        if ( $suffix == 'spreads' && $rr[7] < 0.0002 ) continue; // wrong data 
        
        $rr[0] = "'{$rr[0]}'"; // quote timestamp
        $data = implode($rr, ',');
        $data = str_replace(',,', ',0,', $data); // nulls replace           
        $lines []= "($data)";        
        //echo " inserting data: [$data]\n";           
                      
      } // inner while
      
      
      if (count($lines) > 0)
      {      
          $query = "INSERT IGNORE INTO $table($fields) \n VALUES";
          $query .= implode($lines, ",\n");          
          if ($mysqli->try_query($query))
          {
            // 
            if ($mysqli->affected_rows < 20)
               log_msg("#INSERTED: <font color=blue>$query</font>");
            else
               log_msg(" inserted rows <b>".$mysqli->affected_rows."</b>");
          }  
      }    
      
       
    } // outer while
    
    if ($m < 10)
    {
      log_msg("<font color=green>Optimizing table [$table]</font>");                   
      $mysqli->try_query("OPTIMIZE TABLE $table;");
    }
    
    log_msg("sync completed for $pair");
    echo "________________________________________________________________________________________\n";
    /*
    $tmpt = "$pair\137_tmp";
    make_table_ex($tmpt, $stats_fields, 'ts'); 
       
    $mysqli->try_query("TRUNCATE TABLE $tmpt");  
        
    $query = "INSERT IGNORE $tmpt($sfields) SELECT $sfields FROM $table ORDER BY ts";
    log_msg(" optimizing table: $query");   
    if ($mysqli->try_query($query))
    {    
      $mysqli->try_query( sprintf('RENAME TABLE %1$s__stats TO %1$s__stats_old', $pair) );
      $mysqli->try_query( sprintf('RENAME TABLE %1$s__tmp TO %1$s__stats', $pair) );
    }
    //*/
    
    
    
    return true;
  }
  
  init_db($db_name);
  
  shuffle ($save_pairs);
  
  foreach ($save_pairs as $pair)
  {  
    // if ($pair != 'btc_usd')
    sync_for_pair($pair, 'stats',   $sfields);
    sync_for_pair($pair, 'spreads', $sp_fields);
    if (1)
    {
       $tmp = $remote;
       $remote = $mysqli;
       $mysqli = $tmp;
       sync_for_pair($pair, 'stats',   $sfields);
       sync_for_pair($pair, 'spreads', $sp_fields);
       
       $mysqli = $remote;
       $remote = $tmp;              
    }    
        
  } 
  
  $remote->close();
  $mysqli->close();
?>