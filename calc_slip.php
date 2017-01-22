<?php

  include_once('lib/common.php');
  set_time_limit(30);
  
  $pair = rqs_param('pair', 'btc_usd');
  
  $snap_time = rqs_param('snap_time', 'now'); // '2014-07-23 9:05:00'
  $price_min = rqs_param('price_min', 0);
  $price_max = rqs_param('price_max', 1e12);
  $trade_vol = rqs_param('trade_vol', 1);
  
  
  
  
  $link = mysql_connect('localhost', 'db_reader', 'dbr371x') or die('cannot connect to DB server: '.mysql_error()); // global for all actions
  mysql_select_db("depth_history") or die('cannot select DB depth_history');
  
  
  function convert_depth_data($res)
  {
     $data = array();

     while ($l = mysql_fetch_array($res, MYSQL_ASSOC))
     {
        $ts = $l['ts'];                       // 0
        $price = floatval ($l['price']);      // 1
        $vol   = round ($l['volume'], 3);     // 2
        $flags = $l['flags'];                 // 3
        
        $rec = array ($ts, $price, $vol, $flags);
        array_push($data, $rec);        
     }
     return $data;
  }
     
     
                           
  function get_depth_changes($pair, $filter)
  {
     global $link;    
     $query = "SELECT * FROM $pair"."_diff\n";
     $query .= $filter;     
     $query .= "ORDER BY id\n";     
     $res = mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());
     return convert_depth_data($res);           
  }                         
                           
  function get_full_depth ($pair, $table, $filter)
  {
  
     global $link;    
     $query = "SELECT * FROM $pair"."$table\n";
     $query .= $filter;     
     $query .= "ORDER BY price\n";     
     $res = mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());
     return convert_depth_data($res);
  }                        
                              
  function get_saldo_depth ($pair, $table, $field, $filter)
  {
     global $link;    
     $query = "SELECT SUM($field) as saldo FROM $pair"."$table\n";
     $query .= $filter;
     $res = mysql_query($query) or die("Failed <$query> with errors:\n".mysql_error());
     $row = mysql_fetch_array($res, MYSQL_NUM);     
     if ( count($row) > 0)   
        return $row[0];
     else
        return -1;               
  }                            
                              
  function fsimilar ($a, $b)
  {
     $diff = abs($b - $a);
     return ($diff < $b * 0.000001);
  }                              
                              
  function split_data($data, $part_size)
  {
     // разбивка большого массива котировок, на несколько меньших
     $sdata = array();          
     $part = array();      
     
     foreach ($data as $d) 
     {
        $cnt = count ($part);
        if ($cnt < $part_size)   
            $part []= $d;
        else
        {
           $sdata []= $part;
           $part = array($d);
        }
     }
     
     if (count ($part))
     {
        $sdata []= $part;
        // log_msg(' last part size = '.count($part).'<br/>');
     }
     
     return $sdata;
  }
  
  function join_data($sdata)
  {
    $data = array();
    foreach ($sdata as $part)
      // foreach ($part as $rec)  $data []= $rec; 
      $data = array_merge($data, $part);
    return $data;   
  }                              
                 
  function dump_parts($sdata)
  {
     $i = 0;
     $msg = '';
     foreach ($sdata as $part)
     {
       $msg .= sprintf('%d = %d; ', $i, count($part));
       $i ++;
     }
     log_msg ($msg.'<br/>');    
  }                 
                 
  function find_part($sdata, $price)
  {
  
    $cnt = count($sdata);
    
    for ($i = 0; $i < $cnt; $i ++)
    {
      $part = $sdata[$i];
    
      $last = count($part) - 1;    
      $pmin = $part[0][1];
      $pmax = $part[$last][1];

      // цена левее этой части, или внутри неё
      if ($price < $pmin)
      {
         // log_msg(" for price $price returned part [$pmin..$pmax] <br/>");
         return $i;
      }
             
      if ( $pmin <= $price && $price <= $pmax ) 
          return $i;
    }
        

    $last = $cnt - 1;
    $part = $sdata[$last];
             
    $l = count($part) - 1;    
    $pmin = $part[0][1];
    $pmax = $part[$l][1];    
    
    log_msg(" for price $price returned last part [$pmin..$pmax] <br/>");
    return $last;       // обновление последней части        
  }                 
                              
                              
  function find_row ($data, $price)
  {       
     $cnt = count($data);
     // поиск ближайшей позиции для вставки/замены элемента по цене
     for ($i = 0; $i < $cnt - 1;) 
     {        
       $cp = $data[$i][1];
       $i++;
       $np = $data[$i][1];
       
       if ( $cp <= $price && $price < $np ) // вхождение в диапазон, между предыдущим и текущим элементом
             return $i - 1;          
       if ( $cp > $price )                
             return $i - 1;                 // out range
             
     }
     return ($cnt - 1);          
  }                              
  
  function update_row (&$data, $rec)
  {
    $i = find_row ($data, $rec[1]);
    
    if ( fsimilar ($data[$i][1], $rec[1]) )
    {
    
       // log_msg ("update rec with price = {$rec[1]} vol = {$rec[2]}, at {$data[$i][1]} <br/>");
       $data[$i] = $rec;     // просто обновление        
    }   
    else 
    if ($rec[2] > 0)     
    {
       // 
       $k = $i;
          
       if ($data[$i][1] < $rec[1])                    
           $k = $i + 1; // вставить перед элементом с большей ценой       
           
       // log_msg ("insert rec with price = {$rec[1]} vol = {$rec[2]} in {$data[$i][1]} .. {$data[$k][1]} <br/>");     
       $ins = array ($rec);       
       array_splice ($data, $k, 0, $ins);
       if ($data[$i][1] > $data[$k][1])
           die ("after insert rec [{$rec[1]} , {$rec[2]}], data[$i, $k] = {$data[$i][1]} .. {$data[$k][1]} <br/>");
    } 
   
  }                              
                              
  function get_past_depth($pair, $filter)
  {
     global $snap_time;
    
     $ts = new DateTime ($snap_time, new DateTimeZone('UTC'));
     
     // полные котировки сохраняются каждый час
     $ts_begin = $ts->format('Y-m-d H:00:00');
     $ts_end   = $ts->format('Y-m-d H:00:59');
     
     $flt =  "$filter AND (ts >= '$ts_begin') AND (ts <= '$ts_end') \n";       
     
     $data = get_full_depth ($pair, '_full', $flt);
     // добавление изменений после начала часа
     
     $ts_end   = $ts->format('Y-m-d H:i:s');
     
     $flt = "$filter AND (ts >= '$ts_begin') AND (ts <= '$ts_end') \n";
     
     $changes = get_depth_changes ($pair, $flt);
     
     if (0 == count($changes)) return $data; // possible variant
     
     $fast_method = true;
     
     
     $rcnt = count($data);
     
     // log_msg (" applying changes ".count($changes)."<br/>");
     
     $sdata = split_data($data, 100);
     
     $data = join_data($sdata);
     $ncnt = count($data);
     
     // log_msg ( sprintf( ' items count %d after split = %d, diff = %d  <br/>', $rcnt, $ncnt, $rcnt - $ncnt ) );
     
     // dump_parts($sdata);
     
     foreach ($changes as $c)
     {
        if ($fast_method)
        {
           $i = find_part($sdata, $c[1]);
        
           $part = $sdata[$i];
           // $last = count($part) - 1;    
           // $pmin = $part[0][1];
           // $pmax = $part[$last][1];
           // log_msg(" for price {$c[1]} found part with price-range {$pmin}..{$pmax} <br/>");
        
           update_row ($part, $c);
           
           $sdata[$i] = $part;
        }
        else                      
           update_row ($data, $c);  
     }          
     
     if ($fast_method)
         $data = join_data($sdata);
     
     // dump_parts($sdata);
     
     
     // log_msg ("parts count = ".count($sdata). ' items = '.count($data).'<br/>');    
     
     
     // updated data: only rows with volume
     $udata = array();
     foreach ($data as $d)
      if ($d[2] > 0.01)
          $udata []= $d;
          
     // die("parts count = ".count($sdata). " items = ".count($udata));
          
     
     
     
     // die("DEBUGGING!"); 
     
     return $udata;  
  }
                                
                              
                              
  function saldo_volume($data, $flags, $limit)
  {
    $tz = new DateTimeZone('UTC');
    // price, bids_volume, asks_volume
    $saldo = array (0, 0, 0);
    // по умолчанию цены идут возрастающим порядком. Для бидов надо считать соотв. по убыванию цены
    if ($flags == 1)
        $data = array_reverse($data);
        
    foreach ($data as $d)
    {       
      $f = $d[3];
      
      if ( $saldo[$flags] < $limit && $f == $flags )  
      {
        $saldo [0] = $d[1];   // save price
        // echo ( sprintf( "[$flags] %.5f %.2f <== %.5f %.2f\n", $saldo[0], $saldo[$flags], $d[1], $d[2]));
      }
      
      if ($f > 0)
          $saldo[$f] += $d[2];
       
           
      
    }
    return $saldo;
  }
                               
  
  $filter = "WHERE ($price_min <= price) and (price <= $price_max)"; 
  // $bids_cost = get_depth_saldo($pair, '_last', 'volume * price', $filter." and (flags = 1)\n");
  // $asks_vol =  get_depth_saldo($pair, '_last', 'volume',         $filter." and (flags = 2)\n");
                  
  header("Content-Type: text/plain");                           
  
  // echo("$pair: $bids_cost, $asks_vol");
  
  $d = get_full_depth($pair, '_last', $filter);
   
  $saldo_bids = saldo_volume($d, 1, $trade_vol);
  $saldo_asks = saldo_volume($d, 2, $trade_vol);  
  
  echo("$pair, $trade_vol: {$saldo_bids[0]},{$saldo_asks[0]}\n");
      
  mysql_close($link);
?>