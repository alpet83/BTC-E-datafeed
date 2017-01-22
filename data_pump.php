<?php
  header("Access-Control-Allow-Origin:*");
?>

<!DOCTYPE html>
<html lang="ru">
 <head>
  <meta charset=utf-8>
  <meta name="author" content="alpet">
  <title>BTC-E источник данных Web-Socket v0.1</title>
  <style>
  td{
   vertical-align: top;
  }
  #buy, #sell{
    max-height: 180px;
    overflow: hidden;
  }
  </style>
  
    <script src="http://js.pusher.com/2.2/pusher.min.js" type="text/javascript"></script>
    <script src="js/ajaxsup.js" type="text/javascript"></script>
    <script src="js/jquery-1.11.2.js" type="text/javascript"></script>
    <script src="js/jquery.json-2.4.js" type="text/javascript"></script>
<?php
   include_once('lib/common.php');
   include_once('lib/config.php');

   $state = file_get_contents('trade_state.json');
   echo '    <script type="text/javascript">';
   echo "\n\t\tvar trade_state_json = '$state';\n";
   echo "\t\tvar upd_pairs = new Array";
   printf("('%s');\n", implode($save_pairs, "','"));
   echo "\t</script>\n";
   
   $act = "";
   
   if (isset($_REQUEST['action'])) 
      $act = $_REQUEST['action'];
      
   if ($act == 'upd_state')
   {
      $state = $_POST['trade_state'];
      file_put_contents('trade_state.json', $state);
   }    
      
      
?>      
    
    <script type="text/javascript">
    
    var remote = '10.10.10.50';    
    var ask_last = 0;
    var bid_last = 0;
    // var price_last = 0;
    var recv_count = 0;
    var time_last  = new Date();
    
    // var trade_state = new Object();
    
    var trade_state = $.evalJSON(trade_state_json);         
    
    var actions      = new Array();
    var work_queue   = new Array();  // джобы на выполнении сейчас
    
    var in_progress = new Object();    
    var checks_count = 0;
    var idle_ticks = 0;
    
    var post_queue = new Array();
         
    var nodes_cache = new Object(); // для хранения указателей на узлы под Осла
    var last_min = 0;      
    var last_funds = new Object();
    var last_trade = new Object();
    var depth_data = new Array();
    var depth_last = new Array();  // для фильтрации повторных ложных приемов - на depth_limit позиций к каждому тикеру
    var depth_limit = 64;
    var rqs_count = 0;
    var data_sent = 0;     
        
    
    
    // ============================================ functions ========================================= //
    function dumpObject(data)
    {
      var result = "";
      for (var index in data)
      {
        var val = data[index];
        var vt = typeof val;
        result = result + index + " = ";
        
        if (vt == "number" || vt == "string")
           result = result + String(val) + " ";
        else 
        if (vt == "object") 
           result = result + "{" + dumpObject(val) + "}";
          
        else
           result = result + "@" + vt + " ";   
        
      }
    
      return result;
    }
    
    function elem(id)
    {
       var e = document.getElementById(id);
       if (!e) logMsg("ERROR: document.getElementById not found element '" + id + "'");
       if (e && typeof e.innerHTML == "undefined") alert(id + ".innerHTML == @undefined");
       return e;
        
    }
    
    
    function volcheck(v)
    {
       v = Number(v) * 100;
       v = v - Math.floor(v);              
       if ( v > 0.33 && v < 0.35 ) return true;
       
       return false; 
    }
    
    
    function arval (a, key, def)
    {
       if (typeof a[key] == 'undefined') 
           return def;
       else
          return a[key];
    }

     
    function LZ(num) 
    {
  	   var A = num.toString();
  	   if (A.length == 2) return A;  	   
       if (A.length == 1) return "0" + A;
       	     
	     return "00";
    }
    
    function LZ3 (num) 
    {
  	   var A = num.toString();
       if (A.length >= 3) return A;
  	   if (A.length == 2) return "0" + A;  	   
       if (A.length == 1) return "00" + A;       	     
	     return "000";
    }    

    function dateToStr(d, GMT)
    {
       if (GMT)
         return d.getUTCFullYear() + "-" + LZ(d.getUTCMonth() + 1) + "-" + LZ(d.getUTCDate());
       else
         return d.getFullYear() + "-" + LZ(d.getMonth() + 1) + "-" + LZ(d.getDate());
    }

    function timeToStr(t, GMT)
    {
       var h = "";
       if (GMT)
          h = t.getUTCHours();
       else
          h = t.getHours();         
        
       return LZ(h) + ":" + LZ(t.getMinutes()) + ":" + LZ(t.getSeconds());
    }
    function fullTimeStr(t, sep)
    {
       return LZ(t.getHours()) + sep + LZ(t.getMinutes()) + sep + LZ(t.getSeconds()) + "." + LZ3(t.getMilliseconds());
    }
    
    function setTBodyInnerHTML(tbody, html) 
    {
       var temp = tbody.ownerDocument.createElement('div');
       var save_id = tbody.id;
       temp.innerHTML = '<table>' + html + '</table>';
       tbody.id = tbody.id + "1"; // forgot element
       
       var tab = temp.firstChild; 
       var cl = tab.children;
       var i;       
       for (i = 0; i < cl.length; i ++) // && fc[0]
       {
         var e = cl[i];
         if (e.tagName == "TBODY") 
         {
            e.id = save_id;
            // setHTML ("info3", e.id + "@" + e.tagName + " replacing to " + tbody.id);
            tab = tbody.parentNode;                    
            tab.replaceChild(e, tbody);
            nodes_cache[save_id] = e;
         } 
       }
       
    }
    
    function genTransNo()
    {       
       var t = "0";
       var dt = new Date();
       for (i = 0; i < 10; i++)
       {
         t = fullTimeStr(dt, "") + i.toString();
         if (last_trans_no.indexOf(t) < 0) break;
       }
       last_trans_no.push(t);
       if (last_trans_no.length > 10) last_trans_no.shift();
       return t;       
    }
    
    function logMsg(text)
    {
       var e = elem("debug_log");
       var t = timeToStr(new Date());
       if (e)       
       {
           var lines = e.innerHTML.split("\n");
           while (lines.length > 500) lines.shift();
           var msg = "[" + t + "]. " + text;
           e.innerHTML = lines.join("\n") + msg  + "</br>\n";
           if (console) console.log(msg);
       }           
    } 
    
    function findWork(name, target)
    {
      for (i = 0; i < work_queue.length; i++)
      {
          var w = work_queue[i];
          if (w.name == name && w.target == target) return w;
      }
      return false;
    }
    
    function readyForWork (name, target)
    {
      return (!findWork(name, target) && work_queue.length < 5);
    }
    
    function workFailed(name, target)
    {
      var w = findWork(name, target);
      if (w)
       {
           w.status = "error";
           w.fail_count = w.fail_count + 1;
       }
    
    }
    
    function workSuccess(name, target)
    {
       var w = findWork(name, target);
       if (w) w.status = "complete";
    }

    
    function schedule(cmd, args) 
    { 
      actions[actions.length] = { command: cmd, params: args }; 
    }
    
    
    function processAction()
    {
      setTimeout(processAction, 1000);
      if (actions.length == 0) return;
      var action = actions.shift();
      var cmd = action.command;
      logMsg("processed action: " + cmd);
    }
    
    function addWork(name, target, params)
    {
      var w = new Object();
      w.name = name;
      w.target = target;
      w.status = "new";
      w.fail_count = 0;
      w.timeout    = 300;
      w.params     = params;     
      work_queue.push(w);
      return w;
    }
    
    function handleWork(w)
    {
      w.status = "progress";          
    }
        
    
    var pusher = null; 
    
    var sender   = makeXMLRequest();
    
    var notifier  = makeXMLRequest();
       
    var rq_list = new Array( );   
    var rq_rent = new Array( );
    var rq_urls = new Array( );
    var rqs_num = 0;
       
    function rentBusy(url)
    {
       var count = 0;
       for (var i = 0; i < rq_list.length; i++)
       {       
          rqs = rq_list[i]; 
          if (1 == rqs.readyState && rq_urls[i] && rq_urls[i].search(url) >= 0 ) count++;
       }
      
       return count;
    }
       
       
       
    function rentRqs()
    {
      var st_list = new Array();
      var ct = new Date();
      rqs_count = rqs_count + 1; // pref detector 
       
      for (var i = 0; i < rq_list.length; i++)
      {
         var rqs = rq_list[i];
         
         var elps = ct - rq_rent[i];
         // timeToStr
         if (elps > 1000)
             st_list.push(elps);
         
         if (1 == rqs.readyState && elps > 30000)
         {
             rqs.abort();  
         }    
         
         if (0 == rqs.readyState || 4 == rqs.readyState)
         {
             rqs_num = i;
             rq_rent[i] = ct;
             return rqs;
         }         
      }
      if (rq_list.length >= 128)
          location.reload(); // memory leak prevent
      
      var rqs = makeXMLRequest();
      var i = rq_list.length;
      rqs_num    = i;
      rq_list[i] = rqs;
      rq_rent[i] = ct;
      
      logMsg(" added XMLrequest object, total objects = " + rq_list.length + ", states_list: " + st_list.join());
      return rqs;    
    }   
       
    
    function setHTML(id, text)
    {
       var e = elem(id);
       if (e == null)
       {
           setHTML("info", "ERROR: not found element: " + id);
           return;  
       }
              
       if (typeof text != "undefined")
           e.innerHTML = text.toString();
       else
           e.innerHTML = "NULL";    
    }
 
  

    /*
       trade_state[ticker] = state;       
       var txt = $.toJSON (trade_state);
       sender.open("POST", "quoter.php", true);
       var params = "action=upd_state&trade_state=" + encodeURIComponent(txt);
       sender.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
       sender.send(params);         
         
    */
    var ws = false;
    var ws2 = false; // for remote server 

    function open_socket(server = 'ws://10.10.10.97:8000')
    { 
      var sock = new WebSocket(server);
      sock.onopen = function()          { logMsg('ws: connection established'); }     
      sock.onclose = function()         { logMsg('ws: connection lost!');  ws = false; }
      sock.onmessage = function (event) { logMsg('ws: ' + event); }
      return sock;                                        
    }    
    
    function on_flush()
    {
      // cleanup 
      for (var ticker in depth_data)
      {
          var rec = depth_data[ticker];
          rec.bid = new Array();
          rec.ask = new Array();         
           
      }    
    }
    
    
    function checkDataLag()
    {       
      var now = new Date();   
      var lag = now - time_last;
      setHTML("lag", lag / 1000.0);
      
      var delay = 1000 - (lag % 1000);
      if (delay < 100) delay += 1000;
      
      checks_count ++;
      idle_ticks ++;
      
      if (checks_count > 1000 && lag > 100)
          location.reload(); 

      for (var index in trade_state) 
       if (index != "version")
         {       
            var item = trade_state[index];
            setHTML(index + ".side",   item['side_last']);
            setHTML(index + ".ticker", index);
            setHTML(index + ".price",  item['price_last']);
            setHTML(index + ".qty",    item['qty_last']);                 
            setHTML(index + ".time",   item['time_last']);
         };
         
      var sec = now.getSeconds();   
      var len = 0;
      var data = new Object();
          
      for (var ticker in depth_data)
      {
         data[ticker] = depth_data[ticker];
         len++;
      }
      
      var sel = (sec % 10); 
      var cnt = 0;
      var minute_end = (sec >= 58);

      if (!ws || ws.readyState == 3)
           ws = open_socket();
      if (!ws2 || ws2.readyState == 3)
           ws2 = open_socket('ws://' + remote + ':8000');     
                 
      var txt = $.toJSON(data);
      
      
      
      if (rentBusy("upd_depth.php") == 0 || sec > 50)
      if (sel == 4 || sel == 9)
      {
        var url = "upd_depth.php?pair=all";
        
        if (ws.readyState == 1) 
            ws.send('depth=' + txt);
        else
        {
          var rqs = rentRqs();
          rqs.open("POST", url, true);       
          rqs.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');      
          rqs.send("pair=all&data=" + encodeURIComponent(txt) + "&rqs=" + rqs_num);
          logMsg(" http depth update local ");                  
        }           
               
        url = "http://" + remote + "/upd_depth.php?pair=all";
        
        if (ws2.readyState == 1)
            ws2.send('depth=' + txt);        
        else
        {
          var rqs = rentRqs();
          rqs.open("POST", url, true);       
          rqs.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');      
          rqs.send("pair=all&data=" + encodeURIComponent(txt) + "&rqs=" + rqs_num);
          logMsg(" http depth update remote ");                  
        }
        setHTML('info2', 'rqs_count = ' + rqs_count + ', data_sent = ' + (data_sent / 1024.0) + 'K, sec = ' + sec);
        data_sent = data_sent + txt.length * 2;
        on_flush();
      }      
      
      setTimeout(checkDataLag, delay);   
    }
    

    function onDepth(ticker, data)
    {
       var time_last = new Date();  // received was
       var txt = $.toJSON (data);
       var i = 0;
       
              
       // if (ticker != "btc_rur") return;
       
       // var rec = new Object();
       // rec.time = time_last;       
       // rec.data = data;             
       var rec = null;              
       var flt = null;
              
       if (typeof depth_data[ticker] == 'undefined')
       {
           rec = new Object()
           flt = new Array();
           rec.bid = new Array();
           rec.ask = new Array();
           depth_data[ticker] = rec;    
           depth_last[ticker] = flt;            
       }   
       else
       {
          rec = depth_data[ticker];
          flt = depth_last[ticker];
       }    
          
       for (var i = 0; i < flt.length; i ++)
       if (flt[i] == txt)
       {       
          logMsg("<font color='red'> duplicate data for " + ticker + ": " + txt + "</font>");
          return false;
       }   
       flt.push(txt);
       if (flt.length > depth_limit)
           flt.shift();   
          
       if (ticker == "nvc_btc" || ticker == "nvc_usd")
           logMsg(ticker + " depth last: " + txt);
       
       // depth_data[ticker];       
       
       var ts = dateToStr(time_last, true) + ' ' + timeToStr (time_last, true);
       // setHTML("info4", ticker + " depth rec: " + data.join());       
       //                         
       if (data.ask)       
          for (var i = 0; i < data.ask.length; i++)
          {
             data.ask[i][2] = ts; 
             rec.ask.push (data.ask[i]);
          }  
                      
       if (data.bid)       
          for (var i = 0; i < data.bid.length; i++)
          {
             data.bid[i][2] = ts;
             rec.bid.push (data.bid[i]);
          }  
                           
                               
       // if (ticker == "nvc_usd") logMsg(" nvc_usd history: " + $.toJSON(rec));                              
                     
    }
    

    function onTrade(ticker, data) 
    {            
       var i = data.length - 1;
       setHTML("info3", "data last: " + i.toString());
       if (i < 0) return;        
       var rec = data[i];       
       if (typeof rec == "undefined")
       {
          setHTML("info4", "data.last undefined!");
          return;
       }
       
       if (typeof rec[1] == "undefined")
       {
          setHTML("info4", "data.last[1] undefined!");
          return;
       }
       var state = trade_state[ticker];
       if (typeof state == "undefined" || typeof state.price_ref == "undefined" ) 
           state = { price_last: 0, price_ref: 0 };
           
       var pval = Number (rec[1]);
           
       state['side_last']  = rec[0];
       state['price_last'] = pval;
       state['qty_last']   = Number(rec[2]);
       state['time_last']  = timeToStr (time_last);
           
       var price_ref = arval (state, 'price_ref', 0);      
       setHTML("info5", "last recv: " + rec.join());
       
       recv_count = recv_count + 1;
       setHTML("counter", String(recv_count) + ", checks: " + String(checks_count) + ", idle: " + String(idle_ticks));
       
       time_last = new Date();  // received was
       
       if (ticker != "btc_usd")
          setHTML("info5", "onTrade: " + ticker + "@" + pval);
       
       state['price_ref']  = pval;
       trade_state[ticker] = state;
      
              
       var txt = $.toJSON (trade_state);
       
       // random update if trade.time at 0 sec
       if (time_last.getSeconds() == 0 && sender.readyState == 4)
       {       
          sender.open("POST", "data_pump.php", true);
          var params = "action=upd_state&trade_state=" + encodeURIComponent(txt);
          sender.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
          sender.send(params);
       }
       
       var params="pair=" + ticker + "&data=" + rec.join();
       
       // local sync
       if (ws && ws.readyState == 1)
           ws.send("trade=" + ticker + "," + rec.join());
       else
       {
          var rqs = rentRqs();
          rqs.open("GET", "save_trades.php?" + params, true);
          rqs.send();
       }  

       // remote sync
       if (ws2 && ws2.readyState == 1)
           ws2.send("trade=" + ticker + "," + rec.join());
       else                  
       {
         var rqs = rentRqs();    
         rqs.open("GET", "http://" + remote + "/save_trades.php?" + params, true);
         rqs.send();
       }
    }

    
    function subscribeDepth(ticker, d_callback)
    {
      var channel = pusher.subscribe(ticker + '.depth');           
      channel.bind('depth', d_callback);         
      logMsg("#SUBSCRIBED DEPTH: " + ticker);
    }
    function subscribeTrades(ticker, t_callback)
    {
      var channel = pusher.subscribe(ticker + '.trades');           
      channel.bind('trades', t_callback);         
      logMsg("#SUBSCRIBED TRADES: " + ticker);
    }

    
    function connectPusher()
    {
      pusher = new Pusher('c354d4d129ee0faa5c92');
      upd_pairs.forEach(
         function (pair, i, arr) 
         {
            var cb = function (data){ onDepth(pair, data);}; 
            subscribeDepth(pair, cb);
            
            var cb = function (data){ onTrade(pair, data);}; 
            subscribeTrades(pair, cb);            
         }                               
       ); // foreach                             
    }
    
    
    function afterLoad()
    {                       
      setHTML("info", "afterLoad.1");
      setTimeout(checkDataLag,  1000);      
      setTimeout(connectPusher, 2000);      
      setHTML("info", "afterLoad.2");             
      // var data = new Array(0);
      // data[0] = new Array ("buy", "279.3", "1");
      // onTrade_1(data);
      
      processAction();
      
      // setHTML("info", "afterLoad.3");
    }        
    
    </script>
  
  </head>
  <body onLoad="afterLoad()" style="background-color:#A0A0A0;">
    <h1>Last trades</h1>
    <?php 
              
       function add_hcells($data) 
       { 
         foreach ($data as $text) echo("<th>$text</th>\n"); 
       }
       function add_dcells($data) 
       { foreach ($data as $text) echo("<td>$text</td>\n"); }
       
       function add_ticker_table($ticker)     
       {            
          $tab_header = '<table border="1" width="400" cellpadding="7" style="border-collapse:collapse;">'."\n";
          echo($tab_header); 
          echo("<tr>"); 
          add_hcells( array("Side", "Ticker", "Price", "Qty", "Received") );
          echo("<tr>");
          $flds = array("side", "ticker", "price", "qty", "time");
          foreach ($flds as $fld) add_dcells( array("<div id='$ticker.$fld'></div>") );
          echo("</table>");   
       }

       foreach ($save_pairs as $pair)
       {
       	  add_ticker_table($pair);
       }       
    ?>
    
  
    
    
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
     <tr>
       <td>Target ask price<td><div id="ask_target"></div>
     <tr>
       <td>Target bid price<td><div id="bid_target"></div>  
    </table>


    <h2>Works/Jobs</h2>
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">
      <thead><tr><th width="250">Name<th width="150">Target<th width="300">Status</thead>
      <tbody id="tbody_jobs">
      </tbody>
    </table>
    
    <h2>Status & Log</h2>
    <div id="results">
      results
    </div>
    
    <table border="1" width="700" cellpadding="7" style="border-collapse:collapse;">      
      <tr><td width=200>received records:<td width=500><span id="counter">0</span>  
      <tr><td>Status 1<td><span id="info">WARN: Script not worked!</span>
      <tr><td>Status 2<td><span id="info2">info 2</span></td></tr>
      <tr><td>Status 3<td><span id="info3">info 3</span></td></tr>
      <tr><td>Status 4<td><span id="info4">info 4</span></td></tr>
      <tr><td>Status 5<td><span id="info5">info 5</span></td></tr>      
      <tr><td>Last Error:<td><span id="last_err"></span></td></tr>      
      <tr><td>Data lag:<td> <span id="lag">0 ms</span> sec.</td></tr>
    </table> 
     <p>Debug: <div id="debug_log"></div></p>
  </body>
</html>