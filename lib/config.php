<?php
  $btce_api = 3; 

  $save_pairs = array(
     'btc_usd', 'btc_rur', 
     'dsh_btc', 'dsh_usd', 
     'eth_btc', 'eth_usd', 'eth_rur', 
     'ltc_btc', 'ltc_usd', 
     'nmc_btc', 'nmc_usd', 
     'nvc_btc', 'nvc_usd', 
     'ppc_btc', 'ppc_usd', 
     'usd_rur');
     
  $db_servers = array('');
  $db_server_idx = 0;   
  $db_alt_server = $db_servers[0];
  $db_user = 'btc-e';
  $db_pass = '';
    
  $tmp_data_dir = '/var/www';
  
  function switch_alt_server()
  {
    global $db_servers, $db_server_idx, $db_alt_server;
    $db_server_idx = ($db_server_idx + 1) % count($db_servers);
    $db_alt_server = $db_servers[$db_server_idx]; 
    return $db_alt_server; 
  }

?>
