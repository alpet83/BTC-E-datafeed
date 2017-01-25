<?php
  include_once('lib/common.php');
  include_once('lib/config.php');
  include_once('lib/db_tools.php');  
  include_once('lib/web_socket.php');
  include_once('save_trades.php');  
  include_once('upd_depth.php');
    
  
  set_time_limit(1860); // для ограничения возможных утечек памяти, предполагается циклический запуск скрипта (или по расписанию)
  
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
          // echo "$text\n";
          
          if (strlen($text) < 10) return;
                              
          $data = json_decode($text);          
          
          if (!$link) init_db('depth_history');
          complex_update($data);      
          echo ( str_ts_sq().". #DEPTH: data saved to DB \n" );              
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
  
  echo str_ts_sq()." creating server... \n";
  
  if (!$link) init_db();
  
  $server = new WSDataReceiver();
  
  echo str_ts_sq().". starting work loop ...\n";
  $start = time();    
  file_put_contents('command_ws.txt', 'nope');
  
  while ($server->work() >= 0)
  {
     usleep(1000);
     foreach ($upd_trades as $pair => $val)
     {
       save_trades($pair, true);
       save_bars($pair, true);     
     }                     
     $upd_trades = array();         
     
     $elps = time() - $start;
     
     $cmd = file_get_contents('command_ws.txt');
     $cmd = trim($cmd);
     
     if ($elps >= 1800 || strpos($cmd, 'stop'))
     {
        echo str_ts_sq().". work loop breaking... ";
        break;
     }
          
  }
  
  
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
