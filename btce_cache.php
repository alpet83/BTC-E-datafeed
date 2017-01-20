<?php

  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  $pair = rqs_param ('pair', 'btc_usd');
  $data = rqs_param ('data', 'ticker');
       
  if ($data == "depth" || $data == "trades" || $data == "ticker")     
  {    
     $txt = get_public_data($data, $pair, 3, $params);
     echo($txt);
  }
?>