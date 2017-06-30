<?php
  ob_implicit_flush();
  header("content-type", "text/html;charset=utf-8");   
  set_time_limit(300);  
  include_once('lib/common.php');
  include_once('lib/btc-e.api.php');
  include_once('lib/db_tools.php');

   
?>
<html>
 <head>
  <title>Ether summary depth stats</title> 
 </head>
 <body>
  <pre>
   
<?php
  
  function load_remote($url)
  {  
     $ch = curl_init();       
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
     curl_setopt($ch, CURLOPT_TIMEOUT, 45);
     curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($ch, CURLOPT_URL, $url);
     $result = curl_exec($ch);
     if (!$result) {
     	 printf("curl_exec failed for $url: %s\n", curl_error($ch)); 
     }   
     curl_close($ch);    
     return $result;  
  }
  
  function get_poloniex_ticker($pair)
  {
     $url = sprintf('https://poloniex.com/public?command=returnTicker', $pair);
     return load_remote($url); 
  }
 
  
  function get_poloniex_depth($pair, $limit)
  {
     $url = sprintf('https://poloniex.com/public?command=returnOrderBook&currencyPair=%s&depth=%d', $pair, $limit);
     return load_remote($url); 
  }
  
  $bids_map = array();
  $asks_map = array(); 
      
   
  //  
   
  // phpinfo();
  
  
  function add_depth($asks, $bids, $rate, $round) // accum function
  {
     global $asks_map, $bids_map;
     
     // $asks = $data->asks;
     // $bids = $data->bids;
     
     $sum_ask = 0;
     $vol = 0;
     $prv = 0;

     
     // sell orders
     foreach ($asks as $ask)
     {
        $price  = $ask[0];
        $vol   += $ask[1];
        $price = ceil ( $price * $rate / $round ) * $round;
        if ($price != $prv && $price < 1000)
        {
           // echo " $price = $vol \n";
           $prv = $price;
           if (isset($asks_map[$price]))
              $asks_map [$price] += $vol;
           else
              $asks_map [$price] = $vol;
        }
       
     } 
     
     echo "<i> added asks volume:</i><b> $vol</b>\n";
     
     $vol = 0;
     $prv = 0;
    
    // buy orders - price order down  
     foreach ($bids as $bid)
     {
        $price = $bid[0];
        $price = floor( $price * $rate / $round ) * $round;
        
        if ($price < $round) continue;
        
        $vol   += $bid[1];
        
        if ($price != $prv && $price)
        {
           // echo " $price = $vol \n";
           $prv = $price;
           if (isset($bids_map[$price]))
              $bids_map [$price] += $vol;
           else
              $bids_map [$price] = $vol;
        } // if     
              
     } // foreach bids
     echo "<i> added bids volume:</i><b> $vol</b> \n"; 
  } // add_depth
  
  // echo "Loading data from Poloniex...\n"; 
 
  $tdata = get_poloniex_ticker('BTC_USDT');
  $btc_rate = 2590;
  $eth_rate = 300;
  
  $eth_raise = 52 * 15170; // coins per month
  
  if (strlen($tdata) > 100)
  {
    $tdata = json_decode($tdata);
    if (isset ($tdata->USDT_BTC)) 
    {   
        $t = $tdata->USDT_BTC;
        echo "<!-- \n";
        print_r($t);
        echo "--> \n";
        $btc_rate = $t->highestBid;        
        
    }    
    else
        print_r($tdata);
        
    if (isset ($tdata->USDT_ETH)) 
    {   
        $t = $tdata->USDT_ETH;
        echo "<!-- \n";
        print_r($t);
        echo "--> \n";
        $eth_rate = $t->highestBid;        
        
    }                   
  }  
    
    
  
  
  echo "<h3>Using BTC rate $btc_rate, ETH rate $eth_rate</h3>\n";
  printf ( "Average per month emission %d ETH, cost =<b> $%3.f MILLIONS</b> \n", $eth_raise, $eth_raise * $eth_rate / 1e6 );
  printf ( "Average per year  emission %d ETH, cost =<b> $%3.f MILLIONS</b> \n", 12 * $eth_raise, 12 * $eth_raise * $eth_rate / 1e6 );
  
  function poloniex_load_add($pair, $rate)
  {
  
    echo "Poloniex depths loading for $pair...\n";
    $data = '?';
    $date = utc_time();  
    // check_mkdir($date_dir);
    $ts = $date->format('Y-m-d H:i:s'); 
    
    $cache = "cache/$pair".$date->format('-Ymd.Hi').'.json';
    
    if (file_exists($cache))
      $data = file_get_contents($cache);
    else 
      $data = get_poloniex_depth($pair, 10000); // 'BTC_ETH'      
        
    if (strlen($data) > 1000)
    { 
       file_put_contents($cache, $data);
       $data = json_decode($data);
       if (isset($data->bids))
          add_depth($data->asks, $data->bids, $rate, 25);       
    }
    else
      echo "Invalid data received from Polo: $data </br>";      
        
  } // poloniex_load_add
  
  
  poloniex_load_add('BTC_ETH', $btc_rate);
  poloniex_load_add('USDT_ETH', 1);
 
  
  $self = $_SERVER['SERVER_ADDR'];    
  $mysqli = new mysqli_ex($self, 'db_reader', 'dbr371x');
  
  $btce_asks = array();
  $btce_bids = array();
  
   
  
     
  function unpack_res($res, $rate)
  {
     $data = array();  
     while ($l = $res->fetch_array(MYSQLI_ASSOC))
     {
        // $ts = $l['ts'];                    // 0
        $price = floatval ($l['price']);      // 1
        $vol   = round ($l['volume'], 7);     // 2        
        $rec = array ($price * $rate, $vol);
        array_push($data, $rec);        
     }  
     return $data;
  }   
                           
  function btce_load_depth($pair, $rate)
  {
     echo "Loading BTC-E data [$pair] from local DB...\n";
     global $mysqli, $btce_asks, $btce_bids;            
     $res = $mysqli->select_from('*', $pair.'__asks', ' ORDER BY PRICE');
     $btce_asks = unpack_res($res, $rate);    
     $res = $mysqli->select_from('*', $pair.'__bids', ' ORDER BY PRICE DESC');
     $btce_bids = unpack_res($res, $rate);     
     // print_r($btce_bids);
     add_depth($btce_asks, $btce_bids, 1, 25);
  }    
  
  
  if (0 == mysqli_connect_errno())
  {
      $mysqli->select_db("depth_history") or die('cannot select DB depth_history'); 
      btce_load_depth('eth_btc', $btc_rate);  
      btce_load_depth('eth_usd', 1);
  }   
    
  $mysqli->close();
  
  // results printing.  
  
  $total_coins = 92455463; 
  
  function dump_table($t, $reverse, $color)
  {
    global $total_coins;
  
    echo "<table border=1 cellpadding=4px style='border-collapse:collapse;background-color:$color;'>\n";
    echo "<thread><tr><th>Price<th>Summary<th>Cost<th>Part of Money Supply\n";
    echo "<tbody>\n";
    $saldo = 0;
    $prv = 0;
    $keys = array_keys($t);
    sort($keys);
    if ($reverse) 
        $keys = array_reverse($keys);
    
    
    foreach ($keys as $price)
    { 
      $vol = $t[$price];
    
      $vdiff = $vol - $prv;      
      $prv = $vol;
      
      $saldo += $vdiff * $price;   
                 
      printf("<tr><td>%.1f<td>%.0f<td>\$%.3f M<td>%.5f%%\n", $price, $vol, $saldo / 1e6, $vol * 100 / $total_coins ); 
    }
    echo "</table>\n";
  }
  
  echo "<table><tr>\n";
  echo "<td valign=top><b>saldo asks:</b> \n";
  dump_table($asks_map, true, '#ffc0c0');
  echo "<td valign=top><b>saldo bids:</b> \n";
  dump_table($bids_map, true, '#c0ffc0');
  echo "</table>\n";
  
  
  $data = load_remote('https://api.coinone.co.kr/orderbook/?currency=eth');
 
  $asks_krw = array(); 
  $bids_krw = array();
    
  
  if (strlen($data) > 100)
  {
    $round = 5000;
    $data = json_decode($data);
    $bids = $data->bid;
    $vol = 0;
    $prv = 0;
    $pmax = 0;
    $pmin = 1e9;
    $cnt = count ($bids);
    foreach ($bids as $b)
    {
       $p = $b->price;
       $q = $b->qty;
       $vol += $q;
       $rp = floor($p / $round) * $round;       
       // if ($rp != $prv) echo " $p = $vol \n "; 
       $prv = $rp;    
       $pmax = max($pmax, $p);
       $pmin = min($pmin, $p);
    }
    echo "CoinOne $cnt bids in range [$pmin .. $pmax] = $vol\n"; 
  } 




?>
 </body>
</html>