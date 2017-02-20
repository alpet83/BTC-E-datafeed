<?php
  date_default_timezone_set("Europe/Moscow");
  $err_log = "logs/error.log";
  
  $ws_recv      = false; 
  $session_logs = array();
  
  error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

  function utc_time($ts = 'now')
  {
     return new DateTime ($ts, new DateTimeZone('UTC'));
  }

  function str_ts($ts = null, $tz = null)
  {
    $date = new DateTime($ts, $tz); // ('now', 'Europe/Moscow');
    list($usec, $sec) = explode(" ", microtime());
    
    $usec = sprintf('%.3f', $usec);
    $usec = str_replace('0.', '', $usec); //
    $usec = str_replace('1.', '', $usec); //
    return $date->format('H:i:s.').$usec;
  }
  
  function str_ts_sq($ts = null, $tz = null)
  {
    return '['. str_ts().']';
  }
  
  function precise_time()
  {
     list($usec, $sec) = explode(" ", microtime());
     $usec *= 1000000;      
     $r = array($sec, $usec);
     return $r;
  }
  
  function diff_time_ms($start, $end)
  {  
     return 1000 * ($end[0] - $start[0]) + 0.001 * ($end[1] - $start[1]); 
  }
  
  
  function log_msg($msg, $suffix = "\n")
  {
    global $log_file;
    
    $line = str_ts_sq().". $msg$suffix"; 
   
    if ($log_file)    
        fputs($log_file, $line);
    else
        echo ( $line );
    
    // ob_flush ();
    flush ();
  }

  function log_error($msg, $suffix = "\n")
  {
     global $err_log;
     $trace = debug_backtrace();
     $t = $trace[1];
     $line = sprintf("[%s][%s:%d]. %s%s", str_ts_sq(), $t['file'], $t['line'], $msg, $suffix);
     $f = fopen($err_log, 'a+');
     if (!$f) return;
     fputs($f, $line);
     fclose($f);
  }

  function print_traceback()
  {
     $trace = debug_backtrace();
     // var_dump();
     $sep = ' ';
     $i = 0;
     foreach ($trace as $line)
     {
       $s = sprintf("\t$sep %s:%03d in function [%s]", $line['file'], $line['line'], $line['function'] );
       $sep .= ' ';
       if ($i++ > 0) log_msg($s);          
     }
  }

  function rqs_param($name, $default)
  {
    if (isset($_REQUEST[$name]))
        $v = $_REQUEST[$name];
    else 
        $v = $default;
    return $v;  
  }
  
  function round_step($step)
  {
     $ratio = 1;
     while ($step < 10) 
     {
        $step *= 10;
        $ratio *= 0.1;
     }
     while ($step > 100)
     {
        $step *= 0.1;
        $ratio *= 10;
     }
           
     
     if ($step > 50) $step = 50;
     elseif ($step > 25) $step = 25;     
     elseif ($step > 20) $step = 20;
     else 
      $step = 10; 
     
     return $step * $ratio;
  }                                

  function check_mkdir($path, $attr = 0755)
  {
     if (!file_exists($path))
         if (!mkdir($path, $attr, true))
            log_error(" failed create dir '$path'");
  }
  
  function init_colors($im)
  {
    global $colors;
    $colors['black']    = imagecolorallocate($im, 0, 0, 0);         // background
    $colors['aqua']     = imagecolorallocate($im, 50, 255, 255);
    $colors['orange']   = imagecolorallocate($im, 220, 210, 60);
    $colors['lime']     = imagecolorallocate($im, 0, 255, 0);
    $colors['red']      = imagecolorallocate($im, 255, 0,  0);
    $colors['white']    = imagecolorallocate($im, 255, 255, 255);
    $colors['lt_blue']  = imagecolorallocate($im, 128, 128, 255);
    $colors['purple']   = imagecolorallocate($im, 255,   0, 255);
    $colors['yellow']   = imagecolorallocate($im, 255, 255, 0);     
    $colors['gray']     = imagecolorallocate($im, 100, 100, 100); 
    $colors['silver']   = imagecolorallocate($im, 185, 185, 185);
    
  }

  function draw_horiz_axis ($im, $rect, $v_min, $v_max)
  {
     global $colors;
     $gray = $colors ['gray'];
     $white = $colors ['white'];
     $silver = $colors ['silver'];     
     $lcolor = $colors ['label'];
     
     
     $width   = $rect->width ();
     $v_range = $v_max - $v_min;          
     $x_aps = round ($width / axis_step_x);           // сколько точек на оси
     $v_step = round_step ($v_range / $x_aps);        // value step
     $x_step = $width / $v_range;                     // X step     
     $value = round( $v_min / $v_step + 1) * $v_step; // initial value
     
     
     while ($value <= $v_max)
     {
        $rx = round( $rect->left + ($value - $v_min) * $x_step );
                                   
        imagestring($im, 5, $rx - 10, $rect->bottom + 8, round($value, 5), $white);
        
        $ln_color = $gray;
        if ( fmod($value, $v_step * 2) < $v_step ) $ln_color = $silver;        
        
        imageline($im, $rx, $rect->top,    $rx, $rect->bottom - 1, $ln_color);
        imageline($im, $rx, $rect->bottom, $rx, $rect->bottom + 5, $white);
        $value += $v_step;                   
     }       
  
  }

  function draw_vert_axis ($im, $rect, $v_min, $v_max, $b_right)
  {
     global $colors;
     $gray = $colors ['gray'];
     $white = $colors ['white'];
     $silver = $colors ['silver'];     
     $lcolor = $colors ['label'];
     
     $height  = $rect->height();
     $v_range = $v_max - $v_min;          
     $y_aps = round ($height / axis_step_y);          // сколько точек на оси
     $v_step = round_step ($v_range / $y_aps);        // value step
     $y_step = $height / $v_range;                    // Y step     
     $value = round( $v_min / $v_step + 1) * $v_step; // initial value
     
     
     while ($value <= $v_max)
     {
        $ry = round( $rect->bottom - ($value - $v_min) * $y_step);            
        
        $ln_color = $gray;
              
        if ( fmod($value, $v_step * 2) < $v_step ) $ln_color = $silver;      
              
        imageline($im, $rect->left + 1,  $ry, $rect->right - 1, $ry, $ln_color); // cross chart h-line
                
        if ($b_right)
        { 
          imageline($im, $rect->right, $ry, $rect->right + 5, $ry, $white);
          imagestring($im, 5, $rect->right + 20, $ry - 7, round($value, 5), $lcolor); // draw labels on right axis
        }           
        else
        { 
          $label = round($value, 5);     
          if ($v_min > 10000)    
              $label = sprintf("%.3f M", $label * 1e-6);
          
          imageline($im, $rect->left - 5, $ry, $rect->left, $ry, $white);
          imagestring($im, 5, $rect->left - 70, $ry - 7, $label,  $lcolor); // draw labels on left axis          
        }
                                   
        $value += $v_step;           
     }      
  }                            


  class RECT 
  {
     function RECT($l, $t, $r, $b)
     {
        $this->left   = $l;
        $this->top    = $t;
        $this->right  = $r;
        $this->bottom = $b;
     }
  
     function height()
     {
        return $this->bottom - $this->top;
     }
     
     function width() 
     {
        return $this->right - $this->left;
     }
     
  
  }
                                
  
?>