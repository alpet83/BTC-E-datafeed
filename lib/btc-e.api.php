<?php

$last_url = "?";


function get_cached_data($server, $data, $pair, $params)
{
   global $last_url;
   $ch = curl_init();  
   
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
   curl_setopt($ch, CURLOPT_TIMEOUT, 45);
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');

   $url = "http://$server/get_$data.php?pair=$pair&$params";
   // echo("url = $url\n");	
   curl_setopt($ch, CURLOPT_URL, $url);
   $last_url = $url;
   $result = curl_exec($ch);
   if (!$result) {
   	 printf("curl_exec failed for $url: %s\n", curl_error($ch)); 
   }   
   curl_close($ch);
   return $result;
}


function get_public_data($data, $pair, $api_ver, $params)
{
   global $last_url;

   $ch = curl_init();   
   
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
   curl_setopt($ch, CURLOPT_TIMEOUT, 45);
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');

   $url = "https://wex.nz/api/$api_ver/$pair/$data";
   if ($api_ver == 3) 
       $url= "https://wex.nz/api/3/$data/$pair";
   if (isset($params))
  	$url = "$url?$params";
       
   if ($api_ver == 4)
       $url= "http://darkstalker.ru/wex_cache.php?data=$data&pair=$pair&params=".urlencode($params);

   // echo("url = $url\n");	
   curl_setopt($ch, CURLOPT_URL, $url);

   $last_url = $url;
   $result = curl_exec($ch);
   
   if (!$result) {
   	 printf("curl_exec failed for $url: %s\n", curl_error($ch)); 
   }
   
   curl_close($ch);
   return $result;
}
?>
