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
          echo ( str_ts_sq().". #DBG: processing depth data, size: ".strlen($text)."\n");
          // echo "$text\n";
          
          if (strlen($text) < 10) return;
                              
          $data = json_decode($text);          
          
          if (!$link) init_db('depth_history');
          complex_update($data);          
          return;
      }
      if (strpos($text, "trade=") !== FALSE)
      {
          $text = str_replace('trade=', '', $text);          
          list($ticker, $side, $price, $vol) = explode(',', $text);
          if ($ticker && strlen($ticker) >= 7)
          {
            $upd_trades[$ticker] = true;
            log_msg("$ticker marked as updated");
          }
          return;
      }    
          
    
      log_msg("$text\n");
      
      
    }
  
  };
  
  echo "creating server... \n";
  
  if (!$link) init_db();
  
  $server = new WSDataReceiver();
  
  echo "starting work loop ...\n";  
  while ($server->work() >= 0)
  {
     usleep(1000);
     foreach ($upd_trades as $pair => $val)
     {
       save_trades($pair, true);
       save_bars($pair, true);     
     } 

     $upd_trades = array();         
  }
  // */
  if ($link) $link->close();
    
  echo "work loop complete! \n";   
  sleep(3);
?>
