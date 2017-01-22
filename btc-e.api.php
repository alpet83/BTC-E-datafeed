<?php

$last_url = "?";

function get_public_data($data, $pair, $api_ver, $params)
{
   global $last_url;

   $ch = curl_init();
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
   curl_setopt($ch, CURLOPT_TIMEOUT, 45);
   curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; BTCE PHP client; '.php_uname('s').'; PHP/'.phpversion().')');

   $url = "https://btc-e.nz/api/$api_ver/$pair/$data";
   if ($api_ver == 3) 
       $url= "https://btc-e.nz/api/3/$data/$pair";
        
   if (isset($params))
  	$url .= $params;

   // echo("url = $url\n");	
   curl_setopt($ch, CURLOPT_URL, $url);

   $last_url = $url;
   return curl_exec($ch);
}
?>
