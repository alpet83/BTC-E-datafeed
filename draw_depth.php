<?php

  include_once('lib/common.php');
  set_time_limit(30);
  
  $pair = rqs_param('pair', 'btc_usd');
  
  $snap_time = rqs_param('snap_time', 'now'); // '2014-07-23 9:05:00'
  $price_min = rqs_param('price_min', 0);
  $price_max = rqs_param('price_max', 1e12);
  
  $colors = array();
  
  
  $link = mysql_connect('localhost', 'db_reader', 'dbr371x') or die('cannot connect to DB server: '.mysql_error()); // global for all actions
  mysql_select_db("depth_history") or die('cannot select DB depth_history');
  
  function save_image($im)
  {
     header("Content-type: image/png");
     imagepng($im);   
     imagedestroy($im);             
  }
  
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
     $query = "SELECT * FROM $pair"."__diff\n";
     $query .= $filter;     
     $query .= "ORDER BY ts\n";
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
     
     $data = get_full_depth ($pair, '__full', $flt);
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
          
     
     
     // header("Content-Type: text/plain");
     // die("DEBUGGING!"); 
     
     return $udata;  
  }
                                
                              
                              
  function saldo_volume($data)
  {
    $tz = new DateTimeZone('UTC');
    $saldo = array (0, 0, 0);
    
    foreach ($data as $d)
    {
      $date = new DateTime($d[0], $tz);
      $ts = $date->getTimestamp();
      $saldo[0] = max($saldo[0], $ts);
             
      $f = $d[3];
      $saldo[$f] += $d[2];
    }
    return $saldo;
  }
                               
                              
  function draw_depth ($data, $clr_asks, $clr_bids)
  {
     global $pair, $snap_time, $im, $colors, $width, $height, $red, $lime, $white, $yellow;
     // $res = get_depth_stats($pair, 300);
    
     init_colors($im);
      
    
     $aqua   = $colors['aqua'];
     $orange = $colors['orange'];
     $gray   = $colors['gray'];
     $silver = $colors['silver'];
     
     // $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
     // imagestring($im, 3, $px, 9, $string, $orange);
     // collecting info to array, calc extremums.
      
      
                     
     $l = array();                     
                     
     if (0 == count($data))
     {
        imagestring($im, 5, 50, 50, "No depth data in DB for $pair and snapshot time $snap_time UTC", $red);  // chart caption
        save_image($im);   
        return;
     }
  
     $saldo = saldo_volume($data);
     
     $max_vol = max($saldo[1], $saldo[2]);
     
     if ($max_vol <= 0)
     {
        imagestring($im, 5, 50, 50, "max_vol = $max_vol for $pair", $red);  // chart caption
        save_image($im);
        return;
     }
     
     
  
     // echo(" price range = $min_price..$max_price, volume range = $min_vol..$max_vol\n");
     $cl_left = 40;
     $cl_top  = 40;
     $cl_right = $width - 90;
     $cl_bottom = $height - 40;     
     $cl_width = ($cl_right - $cl_left);
     $cl_height = ($cl_bottom - $cl_top);     
     $cl_center = $cl_top + $cl_height / 2;
     
     imagestring($im, 5, $cl_left + 000, $cl_top - 40, "Graphic DOM for $pair", $lime);        // chart caption
     imagestring($im, 5, $cl_left + 300, $cl_top - 40, "asks volume = {$saldo[2]}", $orange);  // legend
     imagestring($im, 5, $cl_left + 300, $cl_top - 20, "bids volume = {$saldo[1]}", $aqua);    // legend 

     $date = new DateTime('now', new DateTimeZone('UTC'));
     $date->setTimestamp ($saldo[0]);
     $ts = $date->format('H:i:s d-m-y');
     imagestring($im, 5, $cl_left + 1000, $cl_top - 20, "snapshot time = $ts UTC", $yellow);    // legend

     
     $xstep = $cl_width * 1.0 / count($data);
     
     $frame_height = $cl_height / 2;
     
     
     //$p_ystep = $frame_height * 1.0 / $price_range;
     
     $ystep = $cl_height * 1.0 / $max_vol;     
     
     $x = $cl_left;
     
     $px = 0;
     $py = 0;
     $rx = 0;
          
     $i = 0;    
     $last = count($data) - 1;
          
     imageline($im, 0, $cl_top,    $width, $cl_top,    $white);     
     // imageline($im, 0, $cl_center, $width, $cl_center, $white);
     imageline($im, 0, $cl_bottom, $width, $cl_bottom, $white);
          
     imageline($im, $cl_left,  $cl_top, $cl_left,  $cl_bottom, $white);
     imageline($im, $cl_right, $cl_top, $cl_right, $cl_bottom, $white);
     
     // $data = array_reverse($data);
     
     // first - bids
     // second - asks
     
     
     
     
     define ('axis_step_x', 100);
     define ('axis_step_y', 80);
     
     $p_first = $data[0][1];
     $p_last = $data[$last][1];
     
     
     imagestring($im, 5, $cl_left + 700, $cl_top - 40, "price_first = $p_first", $white);    // legend
     imagestring($im, 5, $cl_left + 700, $cl_top - 20, "price_last = $p_last", $white);    // legend
     
     $price_range = $p_last - $p_first;
     $px_step = $cl_width / $price_range;
     
     // draw labels on axis X
     
     /*
     $y_aps = round ($cl_height / axis_step_y); // сколько точек на оси Y

     
     $x_aps = round ($cl_width / axis_step_x); // сколько точек на оси Х
     
     $price_astep = round_step ($price_range / $x_aps);     
     $volume_astep = round_step ($max_vol / $y_aps);
     
     $axis_last_x = -100;
     $axis_last_y = -100;
     
     $price = round( $p_first / $price_astep + 1) * $price_astep;     
     
     
     while (0)
     {
        $x = $cl_left + ($price - $p_first) * $px_step;
        $rx = round($x);                           
        imagestring($im, 5, $rx - 10, $cl_bottom + 8, round($price, 5), $white);
        
        $ln_clr = $gray;
        if ( fmod($price, $price_astep * 2) < $price_astep * 0.1 ) $ln_clr = $silver;        
        
        imageline($im, $rx, $cl_top,    $rx, $cl_bottom - 1, $ln_clr);
        imageline($im, $rx, $cl_bottom, $rx, $cl_bottom + 5, $white);
        $price += $price_astep;                   
     }       
     
     //*/
     
          
     $cl_rect = new RECT($cl_left, $cl_top,  $cl_right, $cl_bottom);
     
     $colors['label'] = $orange;
     draw_horiz_axis ($im, $cl_rect, $p_first, $p_last); // X
     
     $colors['label'] = $white;
     draw_vert_axis($im, $cl_rect, 1, $max_vol, true);  // Y
     
      
     
     $v_curr = $saldo [1]; // start from bids
     
     
     $fprv = 1;
     
     
     foreach ($data as $d)
     {
       $ts = $d[0];
       $price = $d[1];
       $vol   = $d[2];
       $flags = $d[3];      
       
       $y = $cl_bottom - $v_curr  * $ystep;
       $ry = round ($y);
       
       $x = $cl_left + ($price - $p_first) * $px_step;
       $rx = round($x);
       
     
       if ($px > 0 && $vol > 0.0)
       {
         if (1 == $fprv)
            imageline ($im, $px, $py, $rx, $y, $clr_bids);    // bids line
         else   
            imageline ($im, $px, $py, $rx, $y, $clr_asks);     // asks line         
       }
       
       
       if (1 == $flags) 
           $v_curr -= $vol;
       else 
           $v_curr += $vol;
       
     
       $px = $rx;
       $py = $y;
       $fprv = $flags;
       
       
       $i ++;
       
     }     
            
  }
    
  function draw_pair($pair)
  {
     global $price_max, $price_min, $snap_time, $red, $lime;
     $filter = "WHERE price < $price_max ";
     if ($price_min > 0) 
         $filter .= "AND price > $price_min ";
     
     
     $data = array();
     
     if ($snap_time == 'now')  
     {
         $asks = get_full_depth ($pair, '__asks', $filter."\n");
         $bids = get_full_depth ($pair, '__bids', $filter."\n");
         $data = array_merge($bids, $asks);
     }
     else
         $data = get_past_depth ($pair, $filter);
          
     draw_depth($data, $red, $lime);
  }
  
  
  
  // image creating 
  $width = rqs_param('w', 1920); 
  $height = rqs_param('h', 1080);
  if ($width < 400) $width = 400;
  if ($height < 200) $height = 200;
      
  $im = imagecreate($width, $height);
  if (!isset($im)) die("cannot create image $width x $height\n");
  
  $black  = imagecolorallocate($im, 0,     0,   0);         // background    
  $lime   = imagecolorallocate($im, 0,   255,   0);
  $red    = imagecolorallocate($im, 255,   0,   0);
  $white  = imagecolorallocate($im, 255, 255, 255);
  $yellow = imagecolorallocate($im, 255, 255,   0);
  
  
  draw_pair($pair);
  
  save_image($im);
  
  mysql_close($link);
?>