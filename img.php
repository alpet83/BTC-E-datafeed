<?php
  header("Content-type: image/png");
  function rqs_param($name, $default)
  {
    $v = $_REQUEST[$name];
    if (!isset($v)) $v = $default;
    return $v;
  }
  
    
  
  $string = rqs_param('text', 'Hello world!');
  $width = rqs_param('w', 1920); 
  $height = rqs_param('h', 1080); 
  
  
  $im = imagecreate($width, $height);
  if (isset($im)) {
     $black = imagecolorallocate($im, 0, 0, 0);
     $orange = imagecolorallocate($im, 220, 210, 60);
     $px     = (imagesx($im) - 7.5 * strlen($string)) / 2;
     imagestring($im, 3, $px, 9, $string, $orange);
     imagepng($im);
     imagedestroy($im);	
  }
  
?>