<?php
  function fix_prices($price, $vol, &$dest)
  {    
     if ($vol < 0.1)   $dest[0] = $price; 
     if ($vol < 1.0)   $dest[1] = $price; 
     if ($vol < 10)    $dest[2] = $price;
     if ($vol < 100)   $dest[3] = $price;
     if ($vol < 1000)  $dest[4] = $price;
     if ($vol < 10000) $dest[5] = $price;
     return $dest;   
  }


  function save_spreads($pair, $asks, $bids)
  {
     global $ts, $mysqli, $spreads_fields, $d_updates;
     
     if (!$mysqli)
        die("#fatal, mysqli not initialized!");
     
     // $query = sprintf("ALTER TABLE `%s__spreads` CHANGE `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT", $pair);
     if ($d_updates[$pair] < 5)
     {           
        make_table($pair."__spreads", $spreads_fields, ", UNIQUE KEY `TIMESTAMP` (`ts`)");
        $query = " DELETE FROM `$pair\137_spreads` WHERE `buy_0.1` * 0.8 > `sell_0.1` ";
        $mysqli->try_query($query);       
        $query = " DELETE FROM `$pair\137_spreads` WHERE `sell_1` >= `buy_0.1` ";
        $mysqli->try_query($query);
     }
     
     $query = "INSERT INTO $pair".'__spreads (';
     $query .= '`buy_0.1`, buy_1, buy_10, buy_100, buy_1000, buy_10k,';
     $query .= '`sell_0.1`, sell_1, sell_10, sell_100, sell_1000, sell_10k, ts)';
     $query .= "\n VALUES(";    
     
     
     $prices = array(0, 0, 0, 0, 0, 0);
     $lp    = 0;
     $saldo = 0;
     
     $limit = 10000;
     
     foreach ($asks as $a)
     {
       $lp = $a[0]; 
       fix_prices($lp, $saldo, $prices);
       $saldo += $a[1]; // add volume
       fix_prices($lp, $saldo, $prices);
       if ($saldo >= $limit) break;
     }

     $query .= implode(',', $prices);

     log_msg(sprintf(" $pair buy  levels: [%s] ", implode(',', $prices))); 
     
     $saldo = 0;     
          
     // WARN: нужны биды, с уменьшением цены
     $prices = array(0, 0, 0, 0, 0, 0);           
     
     foreach ($bids as $b)
     {        
     
       fix_prices($b[0], $saldo, $prices  );
       $saldo += $b[1]; // add volume
       fix_prices($b[0], $saldo, $prices);
       if ($saldo >= $limit) break;
     }
     
     $query .= ','.implode(',', $prices);
     $query .= ",'$ts');\n";
      
     // echo $query;
     $mysqli->try_query($query);
                                      
     log_msg(sprintf(" $pair sell levels: [%s] \n", implode(',', $prices)));
  
  }
?>