<?php
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');
  include_once('lib/web_socket.php');
  
  include_once('save_trades.php');  
  include_once('upd_depth.php');
  $ws_recv = true;
  
  set_time_limit(1860); // для ограничения возможных утечек памяти, предполагается циклический запуск скрипта (или по расписанию)
  echo str_ts_sq()." creating server... \n"; 
  
  $upd_trades = array();
  
  //*
  class WSDataReceiver extends WebSocketServer
  {   
    function onText($connect, $text)
    {
      global $link, $upd_trades;
      if (strpos($text, "depth=") !== FALSE)
      {
          $text = str_replace('depth=', '', $text);
          echo ( str_ts_sq().". #DEPTH: processing data, size: ".strlen($text)."\n");
          $tstart = pr_time();
          // echo "$text\n";
          
          if (strlen($text) < 10) return;
                              
          $data = json_decode($text);          
          
          if (!$link) init_db('depth_history');
          complex_update($data);
          $elps = pr_time() - $tstart;      
          echo ( str_ts_sq().". #DEPTH: data saved to DB, timing $elps seconds \n" );              
          return;
      }
      if (strpos($text, "trade=") !== FALSE)
      {
          $text = str_replace('trade=', '', $text);          
          list($ticker, $side, $price, $vol) = explode(',', $text);
          if ($ticker && strlen($ticker) >= 7)
          {
            $upd_trades[$ticker] = true;
            echo ( str_ts_sq().". #TRADE: $ticker marked as updated \n" );
            return;
          }          
      }    
          
    
      log_msg("$text\n");
      
      
    }
  
  };
  
  
  if (!$link) init_db();
  
  $server = new WSDataReceiver();
  
  echo str_ts_sq().". starting work loop ...\n";
  $start = time();    
  $cmd_file = getcwd().'/command_ws.txt';
  file_put_contents($cmd_file, 'nope');

  
  
  while ($server->work() >= 0)
  {
     usleep(1000);
     foreach ($upd_trades as $pair => $val)
     {
       save_trades($pair, array(), true);
       save_bars($pair, true);     
     }                     
     $upd_trades = array();         
     
     $elps = time() - $start;
     
     $cmd = file_get_contents($cmd_file);
     $cmd = trim($cmd);
     if (time() % 30 == 27)
     { 
        echo str_ts_sq().". last command = [$cmd]\n";
        sleep(1);
     }
     
     if (0 == $elps % 10 & !$link)  
         init_db();
     
     if ($elps >= 300 & !$link) 
     {
        echo str_ts_sq().". #FATAL: cannot connect to DB. Work loop breaking... \n";
        break;
     }
     
     if (strpos($cmd, 'stop') !== false || $elps >= 1800)
     {
        echo str_ts_sq().". work loop breaking... command = [$cmd] \n";
        break;
     }
          
  }
  
  file_put_contents($cmd_file, '@exit');
  
  // */
  if ($link) $link->close();

  $old_logs = getcwd().'/logs/old/';
  check_mkdir($old_logs);

  foreach ($session_logs as $lname => $val)
  if($val)
  {
    $pi = pathinfo($lname);
    $name = $old_logs.$pi['basename'];
    log_msg("moving $lname to $name");
    rename($lname, $name); 
  }  
  $session_logs = array();  
  echo str_ts_sq().". work loop complete! \n";   
  // sleep();
?>
