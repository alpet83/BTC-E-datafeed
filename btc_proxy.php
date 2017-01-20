<?php   
   include_once("common.php");
   
   $pair = rqs_param ('pair', 'btc_usd');
   $data = rqs_param ('data', 'ticker');
      
   $params = "?";
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');
   
   if ($data == 'ticker') 
   {
     $url = "http://Web-Server/ticker/$pair".'_last.json';
     // echo("url = $url\n");	
     curl_setopt($ch, CURLOPT_URL, $url);
     $res = curl_exec($ch);
     echo($res);   	
   }
   else
    echo("data $data not supported by this proxy\n");
   
   
   curl_close($ch);   
?>


