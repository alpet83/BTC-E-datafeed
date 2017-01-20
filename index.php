<?php
  header("content-	type", "text/html;charset=utf-8");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Графические стаканы BTC-E</title>
<meta http-equive="Content-Type" charset="utf-8">
</head>
<body>
 <a name="btc"></a><H2>BTC/USD</H2>
 <img src="draw_depth.php?w=1800&h=800&pair=btc_usd" /><br/>
 <a name="nvc"></a><H2>NVC/BTC</H2>
 <img src="draw_depth.php?w=1800&h=800&pair=nvc_btc&price_min=0.0003&price_max=0.0009" /><br/>
 <H2>NVC/USD</H2>
 <img src="draw_depth.php?w=1800&h=800&pair=nvc_usd&price_min=0.1&price_max=2" /><br/>
 <a name="ppc></a><H2>PPC/BTC</H2>
 <img src="draw_depth.php?w=1800&h=800&pair=ppc_btc&&price_min=0.0009&price_max=0.005" /><br/>

</body>
</html>
