<?php
  include_once('lib/common.php');
  include_once('lib/db_tools.php');
  
  define ('axis_step_x', 200);
  define ('axis_step_y', 50);
  
  $pair = rqs_param('pair', 'btc_usd');
  $limit = rqs_param('limit', 300);
  
  // $server = $_SERVER['SERVER_ADDR'];
  $server = 'localhost';
  $mysqli = new mysqli_ex($server, 'db_reader', 'dbr371x');

  if ($mysqli->connect_error)
      die("cannot connect to DB server $server: ".$mysqli->connect_error); // global for all actions


  $mysqli->select_db("depth_history"); // or die('cannot select DB depth_history');

  $cl_top    = 20;
  $cl_center = 400;
  $min_price = 0;
  $p_ystep   = 1;

  $colors = array();
    
  function get_spreads_data($pair, $limit)
  {
     global $mysqli;     
     $strict = "ORDER BY ts DESC\n"; // данные нужны с конца!
     $strict .= "LIMIT $limit\n";
     return $mysqli->select_from('*', $pair.'__spreads', $strict);
  }

  function get_depth_stats($pair)
  {
     global $mysqli, $limit;     
     $strict = "ORDER BY ts DESC\n";
     $strict .= "LIMIT $limit\n";     
     $res = $mysqli->select_from('*', $pair.'__stats', $strict) or die("non-continuable error");
     return $res;  
  }

  function calc_y($price)
  {
    global $cl_top, $cl_center, $min_price, $p_ystep;
    
    if (!$price)
         $price = 0;
    
    $pt = $cl_center - ( $price - $min_price ) * $p_ystep;
    
    if ($pt <= $cl_top) 
        return $cl_top + 1; 
    
    if ($pt >= $cl_center)        
        return $cl_center - 1;
        
        
    return $pt;          
  }

  function calc_time_points ($step, $ts_min, $ts_max)
  {    
    define('sec_per_day',  3600 * 24);
    define('sec_per_hour', 3600);
    define('sec_per_min',  60);
    
    $points = array();
    
    // 'week', 'month', 'quarter', 'year'
    // sec_per_day * 30, sec_per_day * 120, sec_per_day * 356 
    
    
    $round_steps = array (10, 15, 30, 60, 300, 600, 900, 1800, 3600, 3600 * 4, 3600 * 12, sec_per_day, sec_per_day * 7); 
    
    
    
    for ($i = 0; $i < count($round_steps) - 1; $i ++)
    {
        if ($step >= $round_steps[$i + 1]) continue;
        $step = $round_steps[$i];
        break;        
    }
    
    
    $ts_curr = round( $ts_min / $step ) * $step; // initial value
    $ts = new DateTime();
    
    // интервал до одного дня
    if ($step <= sec_per_day)
    {
        while ($ts_curr <= $ts_max)    
        {
           $ts->setTimestamp($ts_curr);
           // log_msg("ts_curr = $ts_curr = ".$ts->format('d.m H:i:s'). " ts_max = $ts_max <br/>");        
           $points []= $ts_curr;
           $ts_curr += $step;
        }
        
        // die("time step = $step ");
        return $points;
    }
    
    $points []= $ts_curr;
    
    return $points;    
  }
 
  function draw_time_axis($im, $rect, $t_min, $t_max)
  {
     global $colors;
     $gray = $colors ['gray'];
     $white = $colors ['white'];
     $lcolor = $colors ['label'];
     
     $width  = $rect->width ();
     
     $ts = new DateTime ($t_min);     
     $ts_min = $ts->getTimestamp();
     $ts = new DateTime ($t_max); 
     $ts_max = $ts->getTimestamp();
     
     
     $fmt = 'H:i:s';
     
     $ts_range = $ts_max - $ts_min;
               
     $pts_count = round ($width / axis_step_x);          // сколько точек на оси
     
     $t_step = $ts_range / $pts_count;
     
     if ($t_step >= 60) $fmt = 'H:i';
     if ($t_step >= 3600) $fmt = 'H';
     if ($t_step >= 3600 * 24) $fmt = 'd.m';
     
     
     $points = calc_time_points ($t_step, $ts_min, $ts_max);      // time points
     
     $x_step = $width / $ts_range;                    // X step
     
     
     $ts->setTimestamp($points[0]);
     // imagestring($im, 3, 0, $rect->top + 10, 'ts_min = '. $ts->format('d.m H:i:s'), $lcolor);
     // imagestring($im, 3, 0, $rect->top + 20, "points count = ".count($points), $lcolor);
                   
     $dt_prv = '';
               
     foreach ($points as $ts_curr)          
     {
                    
       $rx = $rect->left + ($ts_curr - $ts_min) * $x_step;       
       if ($rx < $rect->left + 10) continue;
       
       $ts->setTimestamp($ts_curr);      
       
       imageline($im, $rx, $rect->top,    $rx, $rect->bottom - 1, $gray);
       imageline($im, $rx, $rect->bottom, $rx, $rect->bottom + 5, $white);           

       imagestring($im, 3, $rx - 16, $rect->bottom + 10, $ts->format($fmt), $lcolor);
       
       $dt = $ts->format('d M y');
       if ($dt != $dt_prv && $t_step < 3600 * 24)
           imagestring($im, 3, $rx - 25, $rect->bottom + 22, $dt, $lcolor);
       
       $dt_prv = $dt;            
     }
  
     return $x_step;
  }
 
                              
  function draw_pair($pair)
  {
     global $colors, $mysqli, $limit, $cl_top, $cl_center, $min_price, $p_ystep;

     $draw = rqs_param('draw', 'stats');

     $dsp = ($draw == 'spreads');
     
     $spd = get_spreads_data($pair, $limit * 2);

     $sp_data = array();
     
     if ($dsp)
     while ($row = $spd->fetch_array(MYSQLI_ASSOC))
     {
        $ts = $row['ts'];
        unset($row['ts']);
        $sp_data[$ts] = $row;
     }

     $res = get_depth_stats($pair);



     $width = rqs_param('w', 1920); 
     $height = rqs_param('h', 1080); 
     $im = imagecreate($width, $height);
     if (!isset($im)) return;
      
     init_colors ($im); 
     
     
     $aqua       = $colors['aqua'];
     $orange     = $colors['orange'];
     $lime       = $colors['lime'];
     $red        = $colors['red'];
     $white      = $colors['white'];
     $lt_blue    = $colors['lt_blue'];
     $purple     = $colors['purple'];
     $yellow     = $colors['yellow'];
     $gray       = $colors['gray'];
     
     $green_1     = imagecolorallocate($im, 0, 190, 0);
     $green_2     = imagecolorallocate($im, 0, 128, 0);
     $green_3     = imagecolorallocate($im, 0, 96, 0);
     $green_4     = imagecolorallocate($im, 0, 96, 0);

     $red_1       = imagecolorallocate($im, 190, 0, 0);
     $red_2       = imagecolorallocate($im, 128, 0, 0);
     $red_3       = imagecolorallocate($im,  96, 0, 0);
     $red_4       = imagecolorallocate($im,  64, 0, 0);

     
     // print_r($colors);
     // die("test");
     
     // $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
     // imagestring($im, 3, $px, 9, $string, $orange);

     // collecting info to array, calc extremums.
      
     $data = array ();
     $min_price = 1e9;
     $max_price = 0;
     $min_cost = 1e15;
     $max_cost = 0;          
     
     $min_vol = 1e10;
     $max_vol = 0; 
                     
     $l = array();                     


     // loading stats data
     while ($l = $res->fetch_array(MYSQLI_ASSOC))
     {
        $ts = $l['ts'];
        $ba = $l['best_ask'];
        $bb = $l['best_bid'];        
        $cb = $l['cost_bids'];
        $va = $l['volume_asks'];
        $vb = $l['volume_bids'];
               
        $min_price = min($min_price, min($ba, $bb));
        $max_price = max($max_price, max($ba, $bb));
        
        
        // учитывается только стоимость бидов, т.к. аски могут стоить миллиарды )
        if ($cb > 0)
            $min_cost  = min($min_cost, min($cb, $cb));
            
        $max_cost  = max($max_cost, max($cb, $cb));
                
        $min_vol   = min($min_vol, min($va, $vb));
        $max_vol   = max($max_vol, max($va, $vb));
        
        
        
        $points = array ($ts, $ba, $bb, $va, $vb, $cb);
                
        if ($dsp && isset ($sp_data[$ts]))
            $points []= $sp_data[$ts];

        array_push($data, $points);        
     }
     if (0 == count($data))
     {
        imagestring($im, 5, 50, 50, "No stats data in DB for $pair", $lime);  // chart caption
        return;
     }
  
     // echo(" price range = $min_price..$max_price, volume range = $min_vol..$max_vol\n");
     $cl_left = 90;
     $cl_top  = 40;
     $cl_right = $width - 90;
     $cl_bottom = $height - 40;
     
     $cl_width = ($cl_right - $cl_left);
     $cl_height = ($cl_bottom - $cl_top); 
     
     $cl_center = $cl_top + $cl_height / 2;

     
     $price_range = $max_price - $min_price;
     $cost_range = $max_cost - $min_cost;
     $vol_range = $max_vol - $min_vol;
     if ($price_range <= 0) die("bad price range");
     if ($vol_range <= 0) die ("bad vol range");
     
     $cns = explode('_', $pair);
     
     $asks_vol = $data[0][3];
     $bids_vol = $data[0][4];
     $bids_cost = $data[0][5];
     
     $dec = 2;
     if ($min_price < 100)  $dec = 3;
     if ($min_price < 10)   $dec = 4;
     if ($min_price < 1)    $dec = 5;
     if ($min_price < 0.1)  $dec = 6;
     if ($min_price < 0.01) $dec = 7;
         
     imagestring($im, 5, $cl_left + 000, $cl_top - 30, "Depth volume dynamics chart", $lime);  // chart caption
     imagestring($im, 5, $cl_left + 300, $cl_top - 30, "asks volume = $asks_vol {$cns[0]}", $orange);  // legend
     imagestring($im, 5, $cl_left + 600, $cl_top - 40, "bids volume = $bids_vol {$cns[0]}", $aqua);  // legend
     imagestring($im, 5, $cl_left + 600, $cl_top - 20, "bids cost   = $bids_cost {$cns[1]}", $purple);  // legend 
     
     if ($pair != 'btc_usd')
         imagestring($im, 5, $cl_left + 900, $cl_top - 40, sprintf("eq. price = %.$dec".'f', $bids_cost / $asks_vol), $white);  // legend 

         
     // extend ranges          
     $min_price -= $price_range * 0.05;
     $max_price += $price_range * 0.05;
     $min_cost  -= $cost_range  * 0.05;
     $max_cost  += $cost_range  * 0.05;
     $min_vol   -= $vol_range   * 0.05;
     $max_vol   += $vol_range   * 0.05;
          
     $price_range = $max_price - $min_price;
     $cost_range  = $max_cost - $min_cost;
     $vol_range   = $max_vol - $min_vol;
       
     
     
     
     $xstep = $cl_width * 1.0 / count($data);
     
     $frame_height = $cl_height / 2;
     
     $up_rect = new RECT($cl_left, $cl_top,    $cl_right, $cl_center);
     $dn_rect = new RECT($cl_left, $cl_center, $cl_right, $cl_bottom);   
     
     $colors['label'] = $white; 
     
     $dn_rect->right = $dn_rect->left;
     
     $colors['label'] = $purple;     
     
     
     
     $p_ystep = $frame_height * 1.0 / $price_range;
     $c_ystep = $frame_height * 1.0 / $cost_range;
     $v_ystep = $frame_height * 1.0 / $vol_range;
      
     
     $x = $cl_left;
     
     $px = 0;
     $rx = 0;
     $last_yap = 0;
     $last_ybp = 0;     
     $last_yva = 0;
     $last_yvb = 0;     
     $last_ycb = 0;
          
     $i = 0;    
     $last = count($data) - 1;
          
     
     imageline($im, 0, $cl_top,    $width, $cl_top,    $white);     
     imageline($im, 0, $cl_center, $width, $cl_center, $white);
     imageline($im, 0, $cl_bottom, $width, $cl_bottom, $white);          
     imageline($im, $cl_left,  $cl_top, $cl_left,  $cl_bottom, $white);
     imageline($im, $cl_right, $cl_top, $cl_right, $cl_bottom, $white);
     
     $data = array_reverse($data);
     
     $last_axis_x = 0;
     
     $t_rect = new RECT($cl_left, $cl_top, $cl_right, $cl_bottom);
     
     
     $colors['label'] = $white;
     $x_step = draw_time_axis ($im, $t_rect, $data[0][0], $data[$last][0]); 
     
     
     // $ts = new DateTime('now', new DateTimeZone('UTC'));
     date_default_timezone_set('UTC');
     
     $t_first = strtotime ($data[0][0]);
     
     foreach ($data as $d)
     {
       $t = $d[0];                      
       
       $ts = strtotime($t);
       
       $x = $t_rect->left + ($ts - $t_first) * $x_step;
       
       $rx = round($x);                           
       
       /*
       if ( $rx - $last_axis_x > 200 && strstr($ts.';', '00;')  )
       {
           $last_axis_x = $rx;
           imagestring($im, 3, $rx - 50, $cl_bottom + 5, $ts, $white);
           imageline($im, $rx, $cl_top,    $rx, $cl_bottom - 1, $gray);
           imageline($im, $rx, $cl_bottom, $rx, $cl_bottom + 5, $white);           
       } //*/       
              
      
     
       $yap = $cl_center - ( $d[1] - $min_price ) * $p_ystep;
       $ybp = $cl_center - ( $d[2] - $min_price ) * $p_ystep;       
       
       $yva = $cl_bottom - ( $d[3] - $min_vol ) * $v_ystep;                 // volume asks line
       $yvb = $cl_bottom - ( $d[4] - $min_vol ) * $v_ystep;                 // volume bids line
              
       $ycb = $cl_bottom - ( $d[5] - $min_cost ) * $c_ystep;                // cost bids line
     
       
     
       if ($px > 0)
       {
       
         if ($dsp && isset($d[6]))
         {
            $spd = $d[6];
            // $ap = $cl_center - ( $spd['sell_0.1'] - $min_price ) * $p_ystep;            
            $s1p = calc_y ( $spd['sell_1']  );
            $s2p = calc_y ( $spd['sell_10'] );
            $s3p = calc_y ( $spd['sell_100'] );
            $s4p = calc_y ( $spd['sell_1000'] );           
            
            
            // imageline ($im, $px, $ap, $px, $bp, $lime);   // v-line near bids
            imageline ($im, $px, $s1p, $px, $s4p, $green_1);
            imageline ($im, $px, $s2p, $px, $s4p, $green_2);
            imageline ($im, $px, $s3p, $px, $s4p, $green_3);
            
            $v   = $spd['sell_10k'];
            $s5p = calc_y ( $v );
            if ($v > 0)          
                imageline ($im, $px, $s4p, $px, $s5p, $green_4);

            $b1p = calc_y ( $spd['buy_1']  );
            $b2p = calc_y ( $spd['buy_10'] );
            $b3p = calc_y ( $spd['buy_100'] );
            $b4p = calc_y ( $spd['buy_1000'] );
            
            // imageline ($im, $px, $ap, $px, $bp, $lime);   // v-line near bids
            imageline ($im, $px, $b1p, $px, $b2p, $red_1);
            imageline ($im, $px, $b2p, $px, $b3p, $red_2);
            
            if ($b4p < $b3p)
                imageline ($im, $px, $b3p, $px, $b4p, $red_3);
            
            
            $v = $spd['buy_10k'];
            $b5p = calc_y ( $v );
            if ($v > 0 && $b5p < $b4p) 
                imageline ($im, $px, $b4p, $px, $b5p, $red_4);            
         }
         // elseif(!$dsp)
         {
           imageline ($im, $px, $last_yap, $rx, $yap, $red);    // asks line
           imageline ($im, $px, $last_ybp, $rx, $ybp, $lime);   // bids line
         }
         

         imageline ($im, $px, $last_yva, $rx, $yva, $orange); // asks volume
         imageline ($im, $px, $last_yvb, $rx, $yvb, $aqua);   // bids volume

         // imageline ($im, $px, $last_yca, $rx, $yca, $yellow);   // asks cost
         
         if ($last_ycb < $cl_bottom)
             imageline ($im, $px, $last_ycb, $rx, $ycb, $purple);  // bids cost        
         
       }
       
       $last_yap = $yap;
       $last_ybp = $ybp;       

       $last_yva = $yva;
       $last_yvb = $yvb;
              
       $last_ycb = $ycb;
       
     
       $px = $rx;
       
       $i ++;
       
       
     }     
     
     
     draw_vert_axis($im, $up_rect, $min_price, $max_price, true);
     draw_vert_axis($im, $dn_rect, $min_cost,  $max_cost,  false);     
     $dn_rect->right = $up_rect->right;   
     // $dn_rect->left  = $up_rect->right;    
     draw_vert_axis($im, $dn_rect, $min_vol,   $max_vol,   true);
     header("Content-type: image/png");
     imagepng($im);   
     imagedestroy($im);              
  }
    
  
  function dump_pair($pair)
  {     
     global $mysqli;
     echo "timestamp,ask,bid,asks_vol,bids_vol\n";
     $res = get_depth_stats($pair, 300);     
     while ($l = $mysqli->fetch_array($res, MYSQLI_ASSOC))
        echo ("{$l['ts']},{$l['best_ask']},{$l['best_bid']},{$l['volume_asks']},{$l['volume_bids']}\n");    
  }
    
  $out = rqs_param('out', 'chart');
  
  if ($out == 'text') 
  {
     header("Content-Type: text/plain");
     dump_pair($pair);
  }
  elseif ($out == 'chart')   
  {       
     draw_pair($pair);
  }
  
  
  $mysqli->close();
?>