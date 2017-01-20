<pre><?php
  include_once('lib/btc-e.api.php');
  include_once('lib/common.php');
  include_once('lib/db_tools.php');
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
  
  // return false;

  set_time_limit(30);
  
  $date = new DateTime ('now', new DateTimeZone('UTC'));
  $date_dir = "/var/www/ticker/".$date->format('Ymd');
  // check_mkdir($date_dir);

  $ts = $date->format('Y-m-d H:i:s');
  
  define ('id_field',     "int(11) unsigned NOT NULL AUTO_INCREMENT");
  define ('double_field', "double NOT NULL DEFAULT '0'");
  define ('float_field',  "float NOT NULL DEFAULT '0'");
  define ('dt_field',     "datetime NOT NULL");
  


  $ticker_fields = array('id' => id_field);
  $ticker_fields['ts'] = dt_field;
  // $ticker_fields['server_time'] = dt_field;
  $ticker_fields['ticker_id']   = 'int(11) NOT NULL';  
  $ticker_fields['buy']         = float_field;
  $ticker_fields['sell']        = float_field;   
  $ticker_fields['last']        = float_field;
  $ticker_fields['volume']      = float_field;
  $ticker_fields['volume_cur']  = float_field;
  $ticker_fields['updated']     = dt_field;
  
  $pmap_fields = array('id' => id_field);
  $pmap_fields['pair'] = 'varchar(31)';
  
  

  
  $commits = 0; // сколько добавленно в таблицу дифф

  function load_last_ts($pair, $flags)
  {
    $query = "SELECT ts FROM $pair"."_last\n";
    $query .= "WHERE flags = $flags \n";
    // $query .= "ORDER BY id DESC\n";
    $query .= "LIMIT 1;\n";
    // echo("Executing $query\n");
    $result = mysql_query($query); 
    if (!$result) die("Failed <$query> with errors:\n".mysql_error());    
    return  mysql_fetch_array ( $result )[0];
  }

  function save_for_pair($pair)
  {
     global $ts, $date, $date_dir, $ticker_fields, $pmap_fields, $last_url;     
     $path =  "/var/www/ticker/";
     check_mkdir($path);
     $file_name = $path.$pair."_last.json";     
     $txt = get_public_data('ticker', $pair, 4, '');     
     // $txt = str_replace(',[', "\n[", $txt);
     // $txt = str_replace(']],', "]]\n", $txt);
     // $txt = str_replace('[[,', "[\n[", $txt);
     
     $id = pair_id($pair);

     file_put_contents($file_name,  $txt);
     $tab = json_decode($txt);

     if (isset($tab) && isset($tab->$pair) )     
     {
        // && isset($tab->ticker)
        log_msg("received ticker data from $last_url");
        $d = $tab->$pair;      
        // echo (" save ticker data performing pair_id = $id\n ");
        // var_dump($tab);
        $date->setTimestamp($d->updated);
        $upd = $date->format('Y-m-d H:i:s');
        
        $buy = $d->buy;
        $sell = $d->sell;
        $last = $d->last;
        $vol = $d->vol;
        $vol_cur = $d->vol_cur;
        
        
        $add = "INSERT INTO data (ts,ticker_id,buy,sell,last,volume,volume_cur,updated)\n";
        $add .= "VALUES('$ts',$id,$buy,$sell,$last,$vol, $vol_cur,'$upd');";
        echo ("add_row query: $add \n");
        mysql_query($add) or  die("Query failed with errors:\n".mysql_error());      
     }		
	}

  $link = mysql_connect('localhost', 'btc-e', 'u8sqz') or die('cannot connect to DB server: '.mysql_error());
  mysql_select_db("ticker_history") or die('cannot select DB depth_history');


  // make_table("data",      $ticker_fields, false);
  // make_table("pair_map",  $pmap_fields, false);

  save_for_pair('btc_usd');
  $today = date("Y-m-d H:i:s");

  if ($today >= "2017-01-16 01:00:00")
  {
     echo(" today = $today \n");
     save_for_pair('dsh_btc');
     save_for_pair('dsh_usd');
     save_for_pair('eth_btc');
     save_for_pair('eth_usd');

  }


  save_for_pair('nvc_btc');
  save_for_pair('nvc_usd');
  save_for_pair('nmc_btc');
  save_for_pair('nmc_usd');
  save_for_pair('ppc_btc');
  save_for_pair('ppc_usd');
  save_for_pair('ltc_btc');
  save_for_pair('ltc_usd');

  
  mysql_close($link);
?>
