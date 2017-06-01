<?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  ob_implicit_flush();
  
  // return false;

  set_time_limit(300);
  
  $date = utc_time();
  $date_dir = "$tmp_data_dir/ticker/".$date->format('Ymd');
  // check_mkdir($date_dir);

  $ts = $date->format('Y-m-d H:i:s');
  
  define ('id_field',     "int(11) unsigned NOT NULL AUTO_INCREMENT");
  define ('double_field', "double NOT NULL DEFAULT '0'");
  define ('float_field',  "float NOT NULL DEFAULT '0'");
  define ('dt_field',     "datetime NOT NULL");
  


  $ticker_fields = array();  
  // $ticker_fields['server_time'] = dt_field;
  // $ticker_fields['ticker_id']   = 'int(11) NOT NULL';
  $ticker_fields['ts']          = 'TIMESTAMP(3) NOT NULL';
  $ticker_fields['buy']         = float_field;
  $ticker_fields['sell']        = float_field;   
  $ticker_fields['last']        = float_field;
  $ticker_fields['volume']      = float_field;
  $ticker_fields['volume_cur']  = float_field;
  $ticker_fields['updated']     = 'TIMESTAMP(3) NULL';
 
  
  $commits = 0; // сколько добавленно в таблицу дифф
                       

  function save_ticker($pair, $young_age = 10)
  {
     global $mysqli, $log_file, $ts, $date, $date_dir, $ticker_fields,
            $last_url, $db_alt_server, $db_user, $db_pass;

     $log_file = fopen(getcwd()."/logs/save_ticker_$pair.log", "a+");
     

     log_msg("save_ticker begins for $pair");
     $path =  getcwd()."/ticker/";
     
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";
     
     if (!$mysqli)
         init_db();
     $mysqli->select_db('ticker_history');
     
     make_table_ex('ticker_history.'.$pair, $ticker_fields, 'ts', ", UNIQUE KEY `updated` (`updated`), KEY(`volume`)");
     
     $tmp = $mysqli->try_query("SHOW CREATE TABLE $pair");
     $row = $tmp->fetch_array(MYSQLI_NUM);
     if ($row && strpos($row[1], '`volume` float'))
     {          
        log_msg("WRONG SQL:\n" .$row[1]);        
        $query = "ALTER TABLE `ticker_history`.`$pair` \n";
        $query .= "CHANGE COLUMN `volume`     `volume` DOUBLE NOT NULL DEFAULT '0', \n"; 
        $query .= "CHANGE COLUMN `volume_cur` `volume_cur` DOUBLE NOT NULL DEFAULT '0'\n";        
        $mysqli->try_query($query);        
     }

          
     $last_upd = $mysqli->select_value('updated', $pair, "ORDER BY updated DESC");
     $date = utc_time();
     if ($last_upd)
     {
        $ref = utc_time($last_upd);
        $age = $date->getTimestamp() - $ref->getTimestamp();
        if ($age < $young_age)
        {
          log_msg(" update not need, due age = $age < $young_age");
          return false;
        }   
     }
     
     $txt = get_public_data('ticker', $pair, 3, '');
     $date = utc_time();                     
     list($usec, $sec) = explode(" ", microtime());    
     $ms = sprintf('%.3f', $usec);
     $ms = str_replace('0.', '.', $ms);
     $ms = str_replace('1.', '.', $ms);   
        

     file_put_contents($file_name,  $txt);
     $tab = json_decode($txt);

     if (isset($tab) && isset($tab->$pair) )     
     {
        // && isset($tab->ticker)
        log_msg("received ticker data from $last_url");
        $d = $tab->$pair;      
        $date->setTimestamp($d->updated);
        
        $upd = $date->format('Y-m-d H:i:s');
        $ts  = $upd.$ms;


        $fields_tm   = 'ts,updated,'; 
        $fields_std  = 'buy,sell,last,volume,volume_cur';
        $fields_prec = 'ROUND(buy,5),ROUND(sell,5),ROUND(last,5),ROUND(volume,5),ROUND(volume_cur,5)'; 
        

        $insert = "INSERT IGNORE INTO $pair ($fields_tm $fields_std) VALUES\n";

        $start = time();

        // проверка, чего можно вставить перед новой котировкой        
        $remote = init_remote_db($db_user, $db_pass); // select one from accessible server
        if ($remote->connect_error)
        {
          log_msg("#FAILED: cannot connect to remote DB [$db_alt_server] {$remote->connect_error}");
          switch_alt_server();
        }  
        else
        {
           $remote->select_db('ticker_history'); 
           
           $strict = " (updated < '$upd') ";              
                                  
           if ($last_upd)           
               $strict .= "and (updated > '$last_upd')";
           
           log_msg("#DBG: requesting updates from $db_alt_server with strict [$strict] ");
           $rows = $remote->select_from($fields_tm.$fields_prec, $pair,
                       "WHERE $strict LIMIT 16000", MYSQLI_STORE_RESULT);

           $lines = array();                        
           if ($rows)
           {
              log_msg("#DBG: received {$rows->num_rows} rows");
              while ($row = $rows->fetch_array(MYSQLI_NUM))
              {             
                $row[0] = "'{$row[0]}'";
                $row[1] = "'{$row[1]}'";                              
                $lines []= '('.implode($row, ','). ')'; // make lines array
              }           
              $rows->close();
           }  
               
           $remote->close();
           
           if (count($lines) > 0)
           {
              $data = implode($lines, ",\n");
              $add = $insert.$data;
              log_msg(" inserting from remote DB:\n$data");              
              $mysqli->try_query($add); 
           }
        }
        
        if (time() - $start > 10) return false; // много времени ушло, а значит данные уже устарели
                     
        
        $buy  = $d->buy;
        $sell = $d->sell;
        $last = $d->last;
        $vol  = $d->vol;
        $vol_cur = $d->vol_cur;
        $new = sprintf("%.5f,%.5f,%.5f,%.5f,%.5f", $buy,$sell,$last,$vol,$vol_cur);
        
        $last = $mysqli->select_row($fields_prec, $pair, 'ORDER BY ts DESC');
        if ($last)
            $last = implode($last, ',');
        else
            $last = '?';
            
        if ($last != $new)
        {              
           $vals = "('$ts','$upd',$new)";        
           $add = "$insert $vals;";
           log_msg ("add_row query: [$new], last: [$last] \n");
           $mysqli->try_query($add);
           on_data_update('save_ticker', $ts);
        }
        else
          log_msg("OPT: last = new = [$last], skip add");              
     }
     // fclose($log_file);		
	}


  $today = date("Y-m-d H:i:s");

  $pair = rqs_param('pair', '');
  if ('' == $pair && isset($argv[1]))
      $pair = $argv[1]; 
   
  if (strlen($pair) >= 7)
  {
     init_db("trades_history");
     save_ticker($pair);  
  }
  else
  if ($pair == 'all')  
  {
     init_db("trades_history");
     echo "[$today]. #DBG: saving all...\n";
     
     $delay = 0;     
     if (isset($argv[2]))
         $delay = (0 + $argv[2]);
          
     foreach ($save_pairs as $pair)
     {
        save_ticker($pair);
        usleep($delay * 1000);        
     } // foreach
  }  
  
  if ($mysqli) $mysqli->close();
?>
