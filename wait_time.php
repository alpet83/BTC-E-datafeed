<?php
  include_once('lib/common.php');
  $ldt = new DateTime('now');
  $sec = $ldt->format('s') * 1;
  set_time_limit(55);
  echo "waiting for ready...\n";
  if ($sec < 50)   
      sleep(50 - $sec);
  else
  if ($sec < 55)
  {
    list($usec, $sec) = explode(" ", microtime());
    $usec *= 1000000;      
    $adj = 990000;       
    if ($usec < $adj) 
        usleep($adj - $usec + 00);
  }

?>